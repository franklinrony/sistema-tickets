<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel Administrativo (KPIs y Balanceo)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Filtros Superiores -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col sm:flex-row items-end space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Inicio</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Fin</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <div>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Filtrar Reportes
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Overview KPIs Generales -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Resumen de Tickets (Selección)</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-purple-50 dark:bg-purple-900/30 p-4 rounded-xl border border-purple-100 dark:border-purple-800">
                            <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-widest">VIP</h4>
                            <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $ticketTypes['vip'] ?? 0 }}</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                            <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Normal</h4>
                            <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $ticketTypes['normal'] ?? 0 }}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-xl border border-red-100 dark:border-red-800 col-span-2">
                            <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 uppercase tracking-widest">Inmediatos / Manuales</h4>
                            <p class="text-4xl font-black text-gray-900 dark:text-white mt-2">{{ $ticketTypes['inmediato'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Live Queue Monitor Edit -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 overflow-y-auto max-h-80">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700 flex justify-between">
                        <span>Monitor de Turnos Activos</span>
                        <span class="text-sm bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">{{ $activeQueue->count() }} pendientes</span>
                    </h3>
                    
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($activeQueue as $ticket)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ticket->prefix_id }}</p>
                                    <p class="text-xs text-gray-500">Agente asignado: <span class="font-semibold">{{ $ticket->agent ? $ticket->agent->name : 'Nadie / Esperando' }}</span></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority === 'inmediato' ? 'bg-red-100 text-red-800' : ($ticket->priority === 'vip' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $ticket->priority }}
                                    </span>
                                    
                                    <!-- Dropdown for manual priority override (Recalcular Cola) -->
                                    <form method="POST" action="{{ route('tickets.update_priority', $ticket->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="priority" onchange="this.form.submit()" class="text-xs py-1 px-2 border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                            <option value="">Cambiar...</option>
                                            <option value="inmediato">Inmediato (Forzar)</option>
                                            <option value="vip">VIP</option>
                                            <option value="normal">Normal</option>
                                        </select>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <p class="text-gray-500 text-sm italic">No hay tickets activos en la cola mundial.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Tabla de Agentes (KPIs Detallados) -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-gray-100">
                        Rendimiento de Agentes y Control de Cargas
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agente</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tickets en su Cola</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Atendidos (Ayer/Hoy)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Promedio (Min)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pausas C/L</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($agentsData as $agentData)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $agentData['name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $agentData['status'] === 'disponible' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $agentData['status'] === 'offline' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ str_contains($agentData['status'], 'pausa') ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ str_replace('_', ' ', $agentData['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $agentData['current_queue'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-gray-900 dark:text-gray-300">{{ $agentData['attended'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-gray-900 dark:text-gray-300">{{ $agentData['avg_time'] }} mins</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <span class="text-yellow-600" title="Pausas Cortas">{{ $agentData['cortas'] }}</span> / 
                                        <span class="text-red-600" title="Pausas Largas">{{ $agentData['largas'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($agentData['current_queue'] > 0)
                                        <form method="POST" action="{{ route('admin.transfer_queue') }}" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="from_agent_id" value="{{ $agentData['id'] }}">
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 ml-2" title="Transferir tickets estancados a otros agentes">
                                                Transferir Cola
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
