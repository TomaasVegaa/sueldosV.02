<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sueldos Web — Estudio Contable' }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="h-full antialiased text-slate-800">
    <div class="min-h-full flex flex-col">
        <!-- Top Navigation Bar -->
        <header class="bg-slate-900 border-b border-slate-800 text-white sticky top-0 z-50 shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Brand & Active Employer Badge -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('employers.index') }}" class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-indigo-600 to-cyan-500 flex items-center justify-center font-bold text-white shadow-inner">
                            SW
                        </div>
                        <div>
                            <span class="font-bold text-lg tracking-tight text-white">Sueldos<span class="text-indigo-400">Web</span></span>
                            <span class="hidden md:inline-block ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-slate-800 text-slate-400 border border-slate-700">v0.2 Enterprise</span>
                        </div>
                    </a>

                    <!-- Employer Selector Component in Header -->
                    <div class="hidden sm:block">
                        <livewire:header-employer-selector />
                    </div>
                </div>

                <!-- Operator Profile & Actions -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="flex items-center space-x-3 bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-semibold flex items-center justify-center text-xs shadow">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="text-left hidden lg:block">
                                <div class="text-xs font-semibold text-slate-200">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] text-slate-400 flex items-center space-x-1">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>{{ auth()->user()->office ?? 'Oficina' }}</span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Cerrar sesión" class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Iniciar Sesión</a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Body: Navigation Sidebar + Content Area -->
        <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
            <!-- Sidebar Navigation -->
            <aside class="w-64 shrink-0 hidden md:block">
                <nav class="space-y-1 bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                    <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Estudio Contable</div>
                    
                    <a href="{{ route('employers.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('employers.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">🏢</span>
                        <span>Empresas / Empleadores</span>
                    </a>

                    <a href="{{ route('workforce.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('workforce.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">👥</span>
                        <span>Legajos de Personal</span>
                    </a>

                    <a href="{{ route('agreements.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('agreements.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">📜</span>
                        <span>Convenios y Escalas</span>
                    </a>

                    <a href="{{ route('concepts.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('concepts.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">⚙️</span>
                        <span>Conceptos Globales</span>
                    </a>

                    <div class="pt-3 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Operaciones</div>

                    <a href="{{ route('payroll.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('payroll.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">📑</span>
                        <span>Liquidaciones y Recibos</span>
                    </a>

                    <a href="{{ route('catalogs.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('catalogs.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">🏛️</span>
                        <span>Catálogos ARCA</span>
                    </a>

                    <a href="{{ route('audit.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition {{ request()->routeIs('audit.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="mr-3 text-lg">🛡️</span>
                        <span>Registro de Auditoría</span>
                    </a>

                    <div class="pt-3 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Próximas Fases</div>

                    <div class="flex items-center px-3 py-2 text-xs text-slate-400 cursor-not-allowed">
                        <span class="mr-3 text-base opacity-50">📥</span>
                        <span>ARCA Libro Sueldos TXT (Fase 4)</span>
                        <span class="ml-auto text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-500">Pronto</span>
                    </div>
                </nav>

                <!-- System Info Card -->
                <div class="mt-4 p-4 bg-slate-100/80 rounded-xl border border-slate-200/80 text-xs text-slate-600">
                    <div class="font-semibold text-slate-800 mb-1">Garantía de Aislamiento</div>
                    <p class="text-[11px] leading-relaxed text-slate-500">
                        Cada consulta está aislada por <code class="bg-white px-1 py-0.5 rounded text-indigo-600 font-mono text-[10px]">employer_id</code>. Los operadores solo acceden a empresas autorizadas.
                    </p>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 min-w-0">
                <!-- Flash messages -->
                @if (session()->has('success'))
                    <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center space-x-2">
                            <span>✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center space-x-2">
                            <span>⚠️</span>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
