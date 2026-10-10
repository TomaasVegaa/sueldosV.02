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
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-cyan-500 shadow-xl shadow-indigo-500/20 text-white font-black text-2xl mb-3">
                SW
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Sueldos<span class="text-indigo-400">Web</span></h1>
            <p class="text-sm text-slate-400 mt-1">Sistema Interno de Liquidación de Haberes</p>
            <div class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                🏢 Estudio Contable • 2 Oficinas • Multiusuario
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-7 border border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Iniciar Sesión</h2>
            <p class="text-xs text-slate-500 mb-5">Ingrese su usuario o correo y contraseña asignada.</p>

            @if ($errors->any())
                <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-medium flex items-start gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-700 mb-1">Usuario o Correo Electrónico</label>
                    <div class="relative">
                        <input type="text" id="email" name="email" value="{{ old('email', 'admin@estudio.com') }}" autocomplete="username" required autofocus
                               placeholder="admin o admin@estudio.com"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-medium text-slate-700">Contraseña</label>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password" autocomplete="current-password" required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 pr-10 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                        <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="Mostrar/Ocultar contraseña">
                            <svg id="eyeIcon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.144 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" checked class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                        <label for="remember" class="ml-2 block text-xs text-slate-600">Recordar sesión</label>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2">
                    <span>Ingresar al Sistema</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>

            <!-- Atajos de Acceso -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2.5">Acceso Rápido para Operadores</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillCredentials('admin@estudio.com')"
                            class="text-left p-2.5 rounded-lg border border-indigo-100 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-300 transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-indigo-900 group-hover:text-indigo-700">👑 Administrador</span>
                            <span class="text-[10px] text-indigo-600 font-mono font-medium">Cargar</span>
                        </div>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">admin@estudio.com</p>
                    </button>

                    <button type="button" onclick="fillCredentials('operador1@estudio.com')"
                            class="text-left p-2.5 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-100 hover:border-slate-300 transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-800 group-hover:text-slate-900">💼 Operador 1</span>
                            <span class="text-[10px] text-slate-500 font-mono font-medium">Cargar</span>
                        </div>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">operador1@estudio.com</p>
                    </button>
                </div>
                <div class="mt-2 text-[11px] text-slate-400 text-center flex items-center justify-center gap-1.5">
                    <span>Clave inicial:</span>
                    <span class="font-mono text-slate-600 text-[11px]">Pass****123!</span>
                    <span class="text-slate-300">(o <code class="text-slate-600 font-mono">password</code>)</span>
                </div>
            </div>

        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} Sueldos Web • Uso interno autorizado
        </p>
    </div>

    <script>
        function fillCredentials(email) {
            const emailInput = document.getElementById('email');
            const pwdInput = document.getElementById('password');
            if (emailInput && pwdInput) {
                emailInput.value = email;
                pwdInput.value = atob('UGFzc3dvcmQxMjMh');
                emailInput.classList.add('ring-2', 'ring-indigo-500');
                pwdInput.classList.add('ring-2', 'ring-indigo-500');
                setTimeout(() => {
                    emailInput.classList.remove('ring-2', 'ring-indigo-500');
                    pwdInput.classList.remove('ring-2', 'ring-indigo-500');
                }, 600);
            }
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                `;
            } else {
                pwdInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.144 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>
