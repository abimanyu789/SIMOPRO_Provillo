<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMOPRO Provillo</title>
    <meta name="description" content="Login ke Sistem Informasi Manajemen Operasional Provillo">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>
        .form-input { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 12px 16px 12px 44px; font-size: 14px; color: #1e293b; transition: all 0.2s; outline: none; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
        .form-input.error { border-color: #ef4444; }
        .login-bg { background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%); }
        .card-glass { background: rgba(255,255,255,0.97); backdrop-filter: blur(20px); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        .float-anim { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen login-bg flex items-center justify-center p-4 font-sans">

    {{-- Background decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-900/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-4xl grid lg:grid-cols-2 gap-0 rounded-2xl overflow-hidden shadow-2xl">

        {{-- Left Panel - Branding --}}
        <div class="hidden lg:flex flex-col items-center justify-center p-12 text-white"
             style="background: linear-gradient(135deg, #1e3a8a, #1e40af, #2563eb);">
            <div class="float-anim mb-8">
                <div class="w-24 h-24 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20">
                    <i class="ri-shoe-line text-5xl text-blue-200"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold mb-3">SIMOPRO</h1>
            <p class="text-blue-200 text-center text-sm leading-relaxed mb-8">
                Sistem Informasi Manajemen Operasional<br>
                <span class="font-semibold text-white">Provillo</span> — Produsen Sepatu Berkualitas
            </p>
            <div class="space-y-3 w-full max-w-xs">
                @foreach([
                    ['ri-bar-chart-box-line', 'Dashboard Operasional Real-time'],
                    ['ri-shopping-bag-3-line', 'Manajemen Pesanan & Produksi'],
                    ['ri-money-dollar-circle-line', 'Arus Kas & Laporan Keuangan'],
                    ['ri-archive-drawer-line', 'Manajemen Stok Otomatis'],
                ] as [$icon, $text])
                    <div class="flex items-center gap-3 text-sm text-blue-100">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="{{ $icon }} text-blue-300"></i>
                        </div>
                        {{ $text }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right Panel - Login Form --}}
        <div class="card-glass p-10 flex flex-col justify-center">

            {{-- Logo mobile --}}
            <div class="flex items-center gap-3 mb-8 lg:mb-10">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center">
                    <i class="ri-shoe-line text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900 text-lg">SIMOPRO</h2>
                    <p class="text-blue-600 text-xs font-medium">Provillo Management System</p>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mb-1">Selamat Datang!</h3>
            <p class="text-gray-500 text-sm mb-8">Masuk ke akun Anda untuk melanjutkan.</p>

            {{-- Error Message --}}
            @if($errors->any())
                <div class="mb-5 flex items-center gap-3 p-3.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                    <i class="ri-error-warning-line text-lg text-red-500 flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login.post') }}" id="loginForm" novalidate>
                @csrf

                {{-- Email Field --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2" for="email">
                        Email
                    </label>
                    <div class="relative">
                        <i class="ri-mail-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="masukkan email anda"
                            autocomplete="email"
                            class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                            <i class="ri-error-warning-line"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <i class="ri-lock-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="ri-eye-line" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                            <i class="ri-error-warning-line"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                        Ingat saya
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="loginBtn"
                        class="w-full py-3 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/30 flex items-center justify-center gap-2">
                    <span id="btnText">Masuk</span>
                    <i class="ri-arrow-right-line"></i>
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-gray-400">
                © {{ date('Y') }} SIMOPRO Provillo. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        /**
         * Toggle visibility password di form login.
         */
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'ri-eye-off-line' : 'ri-eye-line';
        }

        /**
         * Validasi frontend form login sebelum submit.
         * Menampilkan loading state pada tombol submit.
         */
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');

            if (!email || !password) {
                e.preventDefault();
                return;
            }

            // Tampilkan loading state
            btn.disabled = true;
            btnText.textContent = 'Memproses...';
            btn.classList.add('opacity-80');
        });
    </script>
</body>
</html>
