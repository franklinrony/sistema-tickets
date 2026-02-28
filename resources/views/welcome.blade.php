<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kiosko de Tickets - Bienida</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .dark .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-100 flex items-center justify-center relative overflow-hidden transition-colors duration-500">
    
    <!-- Background Decorators -->
    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-blue-500 rounded-full mix-blend-multiply filter blur-[120px] opacity-30 animate-float"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-500 rounded-full mix-blend-multiply filter blur-[120px] opacity-30 animate-float" style="animation-delay: 2s;"></div>

    <div class="relative z-10 w-full max-w-7xl p-6 flex flex-col xl:flex-row gap-8">
        
        <div class="w-full xl:w-5/12 flex flex-col justify-center">
            <!-- Header -->
            <div class="text-left mb-10">
                <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-4 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
                    Bienvenido
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 font-medium">
                    Por favor, selecciona tu tipo de trámite para generar un ticket
                </p>
            </div>

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-8 p-6 glass-panel rounded-2xl text-center border-l-8 border-l-green-500 flex flex-col items-center transform transition-all !bg-white/90 dark:!bg-gray-800/90" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translateY(20px)" x-transition:enter-end="opacity-100 translateY(0)">
                    <div class="flex items-center text-green-600 dark:text-green-400 mb-2">
                        <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-2xl font-bold">¡Tu Ticket está listo!</h3>
                    </div>
                    <p class="text-4xl font-black tracking-wider text-gray-900 dark:text-white mt-4">{{ session('success') }}</p>
                    <p class="text-gray-700 dark:text-gray-300 mt-4 text-lg font-medium">Por favor, toma asiento y espera ser llamado por la pantalla principal.</p>
                    <button @click="show = false" class="mt-6 text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white underline font-semibold transition-colors">Cerrar Alerta</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-8 p-6 glass-panel rounded-2xl text-center border-l-8 border-l-red-500">
                    <p class="text-xl text-red-600 dark:text-red-400 font-bold">{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">
                <!-- Turno Cliente VIP -->
                <form method="POST" action="{{ route('tickets.store') }}" class="group">
                    @csrf
                    <input type="hidden" name="prefixType" value="V">
                    <button type="submit" class="w-full text-left glass-panel rounded-3xl p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl relative overflow-hidden group border-2 border-transparent hover:border-purple-500/30">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex items-center space-x-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/30 flex-shrink-0">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                </div>
                                <div class="flex-grow">
                                    <h2 class="text-2xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-purple-700 to-indigo-700 dark:from-purple-300 dark:to-indigo-300 mb-1">Cliente VIP</h2>
                                    <p class="text-gray-700 dark:text-gray-200 text-sm font-medium">Acuerdos especiales</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-full shadow-sm text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </div>
                    </button>
                </form>

                <!-- Turno Trámite Normal -->
                <form method="POST" action="{{ route('tickets.store') }}" class="group">
                    @csrf
                    <input type="hidden" name="prefixType" value="N">
                    <button type="submit" class="w-full text-left glass-panel rounded-3xl p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl relative overflow-hidden group border-2 border-transparent hover:border-blue-500/30">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex items-center space-x-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 flex-shrink-0">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div class="flex-grow">
                                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-1">Trámite Normal</h2>
                                    <p class="text-gray-700 dark:text-gray-200 text-sm font-medium">Consultas y gestiones generales</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </div>
                    </button>
                </form>
            </div>

            <div class="mt-8 text-left">
                @if (Route::has('login'))
                    <div class="inline-flex space-x-4 glass-panel px-6 py-3 rounded-full">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">Volver al Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                Acceso Empleados
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>

        <!-- RIGHT COLUMN: MONITOR -->
        <div class="w-full xl:w-7/12 flex flex-col pt-8 xl:pt-0 pl-0 xl:pl-8 border-t-2 xl:border-t-0 xl:border-l-2 border-gray-200 dark:border-gray-800">
            <!-- Llamados en Curso -->
            <div class="mb-8">
                <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6 flex items-center">
                    <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse mr-3"></span>
                    Atendiendo Ahora
                </h3>
                
                @if($activeTickets && $activeTickets->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($activeTickets as $ticket)
                            <div class="glass-panel rounded-2xl p-6 flex items-center justify-between border-l-8 {{ $ticket->priority === 'vip' ? 'border-l-purple-500' : 'border-l-blue-500' }}">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 font-semibold mb-1">MÓDULO / AGENTE</div>
                                    <div class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ explode(' ', $ticket->agent->name)[0] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-4xl font-black {{ $ticket->priority === 'vip' ? 'text-purple-600 dark:text-purple-400' : 'text-blue-600 dark:text-blue-400' }} tracking-tighter">{{ $ticket->prefix_id }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="glass-panel rounded-2xl p-8 text-center text-gray-500 dark:text-gray-400 font-medium">
                        Todos los agentes están disponibles o en espera de nuevos clientes.
                    </div>
                @endif
            </div>

            <!-- Últimos 10 Atendidos -->
            <div>
                <h3 class="text-xl font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                    Historial Reciente
                </h3>
                
                @if($recentTickets && $recentTickets->count() > 0)
                    <div class="glass-panel rounded-2xl overflow-hidden divide-y divide-gray-200/50 dark:divide-gray-700/50">
                        @foreach($recentTickets as $ticket)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <span class="inline-flex w-12 h-12 rounded-full items-center justify-center font-bold text-sm {{ $ticket->priority === 'vip' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                        {{ $ticket->prefix_id }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">Completado por {{ explode(' ', $ticket->agent->name)[0] }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($ticket->completed_at)->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300 rounded-full text-xs font-bold uppercase">Finalizado</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-400 italic">No hay tickets finalizados el día de hoy aún.</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
