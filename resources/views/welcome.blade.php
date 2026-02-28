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

    <div class="relative z-10 w-full max-w-4xl p-6">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-4 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
                Bienvenido
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 font-medium">
                Por favor, selecciona tu tipo de trámite para generar un ticket
            </p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-8 p-6 glass-panel rounded-2xl text-center border-l-8 border-l-green-500 flex flex-col items-center transform transition-all" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translateY(20px)" x-transition:enter-end="opacity-100 translateY(0)">
                <div class="flex items-center text-green-600 dark:text-green-400 mb-2">
                    <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-2xl font-bold">¡Tu Ticket está listo!</h3>
                </div>
                <p class="text-4xl font-black tracking-wider text-gray-900 dark:text-white mt-4">{{ session('success') }}</p>
                <p class="text-gray-500 dark:text-gray-400 mt-4 text-lg">Por favor, toma asiento y espera ser llamado por la pantalla principal.</p>
                <button @click="show = false" class="mt-6 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline">Cerrar</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-6 glass-panel rounded-2xl text-center border-l-8 border-l-red-500">
                <p class="text-xl text-red-600 dark:text-red-400 font-bold">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Turno Cliente VIP -->
            <form method="POST" action="{{ route('tickets.store') }}" class="group">
                @csrf
                <input type="hidden" name="prefixType" value="V">
                <button type="submit" class="w-full text-left glass-panel rounded-3xl p-8 transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl relative overflow-hidden group border-2 border-transparent hover:border-purple-500/30">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-purple-500/30">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-indigo-600 dark:from-purple-400 dark:to-indigo-400 mb-4">Cliente VIP</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-lg flex-grow">Atención preferencial para clientes registrados con acuerdos especiales.</p>
                        <div class="flex items-center text-purple-600 dark:text-purple-400 font-semibold mt-6 group-hover:translate-x-2 transition-transform">
                            <span>Generar Ticket</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                    </div>
                </button>
            </form>

            <!-- Turno Trámite Normal -->
            <form method="POST" action="{{ route('tickets.store') }}" class="group">
                @csrf
                <input type="hidden" name="prefixType" value="N">
                <button type="submit" class="w-full text-left glass-panel rounded-3xl p-8 transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl relative overflow-hidden group border-2 border-transparent hover:border-blue-500/30">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-blue-500/30">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">Trámite Normal</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-lg flex-grow">Gestiones, consultas, o apertura de nuevos trámites sin acuerdo preferencial.</p>
                        <div class="flex items-center text-blue-600 dark:text-blue-400 font-semibold mt-6 group-hover:translate-x-2 transition-transform">
                            <span>Generar Ticket</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                    </div>
                </button>
            </form>

        </div>

        <div class="mt-16 text-center">
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
</body>
</html>
