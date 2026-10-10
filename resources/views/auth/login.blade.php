<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al Sistema — Sueldos Web Estudio Contable</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> body { font-family: 'Inter', system-ui, sans-serif; } </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-800">
    <div class="max-w-md w-full">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-cyan-500 shadow-xl shadow-indigo-500/20 text-white font-black text-2xl mb-4">
                SW
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Sueldos<span class="text-indigo-400">Web</span></h1>
            <p class="text-sm text-slate-400 mt-1">Sistema Interno de Liquidación de Haberes</p>
            <div class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                🏢 Estudio Contable • 2 Oficinas • Multiusuario
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Iniciar Sesión</h2>
            <p class="text-xs text-slate-500 mb-6">Ingrese con su usuario de operador o administrador asignado.</p>

            @if ($errors->any())
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-700 mb-1">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@estudio.test') }}" required autofocus
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-medium text-slate-700">Contraseña</label>
                        <span class="text-[11px] text-slate-400">Predeterminada: Password123!</span>
                    </div>
                    <input type="password" id="password" name="password" value="Password123!" required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                    <label for="remember" class="ml-2 block text-xs text-slate-600">Recordar mi sesión en este equipo</label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition shadow-md shadow-indigo-600/30">
                    Ingresar al Panel
                </button>
            </form>

            <!-- Quick Operator Switcher for Local Testing -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-3">Acceso Rápido de Prueba (5 Operadores):</span>
                <div class="grid grid-cols-1 gap-1.5 text-xs">
                    @foreach ($operators as $op)
                        <button type="button" 
                                onclick="document.getElementById('email').value='{{ $op->email }}'; document.getElementById('password').value='Password123!';"
                                class="text-left px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 text-slate-700 hover:text-indigo-700 transition flex items-center justify-between">
                            <span class="font-medium truncate">{{ $op->name }}</span>
                            <span class="text-[10px] text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ $op->office ?? 'Estudio' }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} Sueldos Web • Aislamiento Estricto por CUIT
        </p>
    </div>
</body>
</html>
