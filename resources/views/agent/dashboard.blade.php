<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel del Agente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alertas -->
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna 1: Estado y Pausas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Mi Disponibilidad</h3>
                        <div class="flex items-center">
                            @if($user->status === 'disponible')
                                <span class="h-3 w-3 rounded-full bg-green-500 mr-2"></span>
                                <span class="text-green-600 font-bold">Disponible</span>
                            @elseif($user->status === 'offline')
                                <span class="h-3 w-3 rounded-full bg-red-500 mr-2"></span>
                                <span class="text-red-500 font-bold">Offline</span>
                            @else
                                <span class="h-3 w-3 rounded-full bg-yellow-500 mr-2 animate-pulse"></span>
                                <span class="text-yellow-500 font-bold capitalize">{{ str_replace('_', ' ', $user->status) }}</span>
                            @endif
                        </div>
                    </div>

                    @if(!$activePause)
                        <form method="POST" action="{{ route('agent.toggle') }}" class="mb-8">
                            @csrf
                            <button type="submit" class="w-full text-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $user->status === 'offline' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700' }} focus:outline-none transition-colors">
                                {{ $user->status === 'offline' ? 'Abrir Estación (Conectar)' : 'Cerrar Estación (Offline)' }}
                            </button>
                        </form>

                        @if($user->status !== 'offline')
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Solicitar Pausa Rápida</h4>
                            <form method="POST" action="{{ route('pauses.store') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="type" value="corta">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Motivo</label>
                                    <select name="reason" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:text-white">
                                        <option value="Ir al baño">Ir al Baño</option>
                                        <option value="Tomar un receso">Tomar Receso (Pausa Activa)</option>
                                        <option value="Atender llamada del jefe">Llamada del Jefe</option>
                                        <option value="Hora de Almuerzo">Almuerzo</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                                    Iniciar Pausa
                                </button>
                            </form>
                        </div>
                        @endif
                    @else
                        <!-- Active Pause Screen -->
                        <div class="text-center py-6 bg-yellow-50 dark:bg-yellow-900/30 rounded-xl border border-yellow-200 dark:border-yellow-700">
                            <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Pausa Activa</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-6 font-medium">{{ $activePause->reason }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Iniciada: {{ $activePause->start_time->diffForHumans() }}</p>

                            <form method="POST" action="{{ route('pauses.update', $activePause->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none transition-colors">
                                    Reanudar Trabajo
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Columna 2 y 3: Ticket actual y Cola -->
                <div class="lg:col-span-2">
                    <!-- TICKET ACTUAL -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 min-h-[300px] flex flex-col justify-center items-center relative border-t-8 border-indigo-500">
                        @if($currentTicket && !$activePause)
                            
                            @if($currentTicket->status === 'pending')
                                <div class="text-center w-full">
                                    <h3 class="text-xl font-medium text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Próximo Ticket a Atender</h3>
                                    <div class="text-6xl font-black text-gray-900 dark:text-white my-8 tracking-tighter drop-shadow-md">
                                        {{ $currentTicket->prefix_id }}
                                    </div>
                                    <div class="inline-block px-4 py-1 rounded-full text-sm font-bold uppercase mb-8 {{ $currentTicket->priority === 'vip' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $currentTicket->priority }}
                                    </div>
                                    <form method="POST" action="{{ route('agent.attend_ticket', $currentTicket->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-8 py-4 bg-indigo-600 text-white rounded-full font-bold text-lg hover:bg-indigo-700 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                            Llamar y Atender
                                        </button>
                                    </form>
                                </div>
                            @elseif($currentTicket->status === 'in_progress')
                                <div class="text-center w-full">
                                    <div class="animate-pulse flex justify-center mb-4">
                                        <span class="h-4 w-4 rounded-full bg-green-500"></span>
                                    </div>
                                    <h3 class="text-lg font-medium text-green-600 dark:text-green-400 uppercase tracking-widest mb-2">Atendiendo Actualmente</h3>
                                    <div class="text-7xl font-black text-indigo-600 dark:text-indigo-400 my-8 tracking-tighter drop-shadow-md">
                                        {{ $currentTicket->prefix_id }}
                                    </div>
                                    <p class="text-sm text-gray-500 mb-8 font-mono">Tiempo transcurrido: {{ \Carbon\Carbon::parse($currentTicket->attended_at)->diffForHumans(null, true) }}</p>
                                    <form method="POST" action="{{ route('agent.complete_ticket', $currentTicket->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-8 py-4 bg-emerald-500 text-white rounded-full font-bold text-lg hover:bg-emerald-600 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                            Finalizar Gestión
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <div class="text-center opacity-50">
                                <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">Sin tickets pendientes</h3>
                                <p class="text-gray-500 dark:text-gray-400">Estás al día o hay alguien más atendiendo. Sigue así.</p>
                            </div>
                        @endif
                    </div>

                    <!-- MI COLA PENDIENTE -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center justify-between">
                            <span>Espera en tu Cola Especializada</span>
                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-1 px-3 rounded-full text-sm">{{ $myQueue->count() }} tickets</span>
                        </h3>
                        
                        @if($myQueue->count() > 0)
                            <div class="flex overflow-x-auto space-x-4 pb-4 snap-x">
                                @foreach($myQueue as $qTicket)
                                    <div class="flex-none snap-start w-48 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center justify-center transition-transform hover:scale-105">
                                        <span class="text-2xl font-bold text-gray-800 dark:text-white mb-2">{{ $qTicket->prefix_id }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full uppercase font-bold {{ $qTicket->priority === 'vip' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' }}">
                                            {{ $qTicket->priority }}
                                        </span>
                                        <span class="text-xs text-gray-400 mt-2">{{ $qTicket->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">No tienes más personas esperando en tu fila virtual actual.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
