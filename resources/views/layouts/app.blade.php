<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIMOPRO Provillo</title>
    <meta name="description" content="Sistem Informasi Manajemen Operasional Provillo — Platform digital untuk UMKM sepatu">

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Remix Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    {{-- Tailwind CSS via CDN (dev mode) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind CSS untuk SIMOPRO
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        // Palet warna utama Provillo
                        primary: {
                            50:  '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
                            300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6',
                            600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af',
                            900: '#1e3a8a', 950: '#172554',
                        },
                        sidebar: '#0f172a',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
                        slideIn: { '0%': { transform: 'translateY(-8px)', opacity: 0 }, '100%': { transform: 'translateY(0)', opacity: 1 } },
                    }
                }
            }
        }
    </script>

    {{-- Styles kustom tambahan --}}
    <style>
        /* ========================
           SCROLLBAR STYLING
           ======================== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ========================
           SIDEBAR NAVIGATION
           ======================== */
        .sidebar-link {
            display: flex; align-items: center; gap: 10px; padding: 10px 16px;
            border-radius: 8px; color: #94a3b8; font-size: 14px; font-weight: 500;
            transition: all 0.2s ease; text-decoration: none; margin: 2px 0;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-link.active { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,0.4); }
        .sidebar-link i { font-size: 18px; width: 20px; text-align: center; }

        .sidebar-group-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
            color: #475569; padding: 12px 16px 4px; margin-top: 4px;
        }

        /* ========================
           BADGE STATUS
           ======================== */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; }
        .badge-info    { background: #dbeafe; color: #1d4ed8; }
        .badge-primary { background: #ede9fe; color: #6d28d9; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        /* ========================
           MODAL OVERLAY
           ======================== */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-overlay.active { display: flex; animation: fadeIn 0.2s ease; }
        .modal-box { background: #fff; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.2); width: 100%; max-height: 90vh; overflow-y: auto; }

        /* ========================
           CARD
           ======================== */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06); }

        /* ========================
           FORM ELEMENTS
           ======================== */
        .form-input {
            width: 100%; border: 1.5px solid #e2e8f0; border-radius: 8px;
            padding: 10px 14px; font-size: 14px; color: #1e293b;
            transition: all 0.2s; outline: none; background: #fff;
        }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .form-input.error { border-color: #ef4444; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 6px; }
        .form-error { font-size: 12px; color: #ef4444; margin-top: 4px; }

        /* ========================
           BUTTONS
           ======================== */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-primary:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.35); }
        .btn-danger  { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; }
        .btn-danger:hover  { background: linear-gradient(135deg, #dc2626, #b91c1c); transform: translateY(-1px); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; }
        .btn-success:hover { background: linear-gradient(135deg, #16a34a, #15803d); transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1.5px solid #e2e8f0; color: #475569; }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; }
        .btn-icon { padding: 7px; border-radius: 7px; }

        /* ========================
           TABLE
           ======================== */
        .table-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .table-row { transition: background 0.15s; }
        .table-row:hover { background: #f8fafc; }

        /* ========================
           ALERT / TOAST
           ======================== */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); min-width: 300px; animation: slideIn 0.3s ease; }
        .toast-success { background: #fff; border-left: 4px solid #22c55e; }
        .toast-error   { background: #fff; border-left: 4px solid #ef4444; }
        .toast-warning { background: #fff; border-left: 4px solid #f59e0b; }

        /* ========================
           SKELETON LOADING
           ======================== */
        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        /* ========================
           STAT CARD
           ======================== */
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; transition: all 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

        /* ========================
           EMPTY STATE
           ======================== */
        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center; color: #94a3b8; }
        .empty-state i { font-size: 56px; margin-bottom: 16px; opacity: 0.5; }
        .empty-state h3 { font-size: 18px; font-weight: 600; color: #64748b; margin-bottom: 8px; }
        .empty-state p { font-size: 14px; max-width: 360px; }

        /* Sidebar mobile toggle */
        #sidebar { transition: transform 0.3s ease; }
        @media (max-width: 1024px) {
            #sidebar.closed { transform: translateX(-100%); }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">

    {{-- ========================
         SIDEBAR
         ======================== --}}
    <aside id="sidebar"
           class="fixed top-0 left-0 h-full w-64 flex flex-col z-40 overflow-hidden"
           style="background: #0f172a;">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            @php $pengaturan = \App\Models\Pengaturan::getSetting(); @endphp
            @if($pengaturan->logo)
                <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="h-9 w-9 rounded-lg object-cover">
            @else
                <div class="h-9 w-9 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center">
                    <i class="ri-shoe-line text-white text-lg"></i>
                </div>
            @endif
            <div>
                <h1 class="text-white font-bold text-sm leading-tight">{{ $pengaturan->nama_usaha }}</h1>
                <p class="text-blue-400 text-xs font-medium">SIMOPRO</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="ri-dashboard-3-line"></i>
                <span>Dashboard</span>
            </a>

            {{-- Data Master --}}
            <p class="sidebar-group-label">Data Master</p>
            <a href="{{ route('produk.index') }}"
               class="sidebar-link {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                <i class="ri-shoe-line"></i>
                <span>Produk</span>
            </a>
            <a href="{{ route('bahan-baku.index') }}"
               class="sidebar-link {{ request()->routeIs('bahan-baku.*') ? 'active' : '' }}">
                <i class="ri-box-3-line"></i>
                <span>Bahan Baku</span>
            </a>
            <a href="{{ route('karyawan.index') }}"
               class="sidebar-link {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
                <i class="ri-team-line"></i>
                <span>Karyawan</span>
            </a>
            <a href="{{ route('customer.index') }}"
               class="sidebar-link {{ request()->routeIs('customer.*') ? 'active' : '' }}">
                <i class="ri-user-star-line"></i>
                <span>Customer</span>
            </a>

            {{-- Operasional --}}
            <p class="sidebar-group-label">Operasional</p>
            <a href="{{ route('pesanan.index') }}"
               class="sidebar-link {{ request()->routeIs('pesanan.*') ? 'active' : '' }}">
                <i class="ri-shopping-bag-3-line"></i>
                <span>Pesanan</span>
                @php $pendingCount = \App\Models\Pesanan::where('status', 'pending')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('produksi.index') }}"
               class="sidebar-link {{ request()->routeIs('produksi.*') ? 'active' : '' }}">
                <i class="ri-tools-line"></i>
                <span>Produksi</span>
            </a>

            {{-- Stok --}}
            <p class="sidebar-group-label">Stok</p>
            <a href="{{ route('stok-produk.index') }}"
               class="sidebar-link {{ request()->routeIs('stok-produk.*') ? 'active' : '' }}">
                <i class="ri-archive-drawer-line"></i>
                <span>Stok Produk</span>
                @php $stokAlert = \App\Models\StokProduk::where(fn($q) => $q->where('jumlah_stok', '<=', 0)->orWhereColumn('jumlah_stok', '<', 'stok_minimum'))->count(); @endphp
                @if($stokAlert > 0)
                    <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full px-2 py-0.5">{{ $stokAlert }}</span>
                @endif
            </a>
            <a href="{{ route('stok-bahan-baku.index') }}"
               class="sidebar-link {{ request()->routeIs('stok-bahan-baku.*') ? 'active' : '' }}">
                <i class="ri-stack-line"></i>
                <span>Stok Bahan Baku</span>
            </a>

            {{-- Keuangan --}}
            <p class="sidebar-group-label">Keuangan</p>
            <a href="{{ route('arus-kas.index') }}"
               class="sidebar-link {{ request()->routeIs('arus-kas.*') ? 'active' : '' }}">
                <i class="ri-money-dollar-circle-line"></i>
                <span>Arus Kas</span>
            </a>

            {{-- Sistem --}}
            <p class="sidebar-group-label">Sistem</p>
            <a href="{{ route('pengaturan.index') }}"
               class="sidebar-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
                <i class="ri-settings-3-line"></i>
                <span>Pengaturan</span>
            </a>
        </nav>

        {{-- User Profile --}}
        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                @if(auth()->user()->foto)
                    <img src="{{ auth()->user()->foto_url }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-blue-500/40" alt="">
                @else
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-400 text-xs truncate">{{ auth()->user()->jabatan }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Logout">
                        <i class="ri-logout-box-r-line text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden" onclick="toggleSidebar()"></div>

    {{-- ========================
         MAIN CONTENT AREA
         ======================== --}}
    <main class="lg:ml-64 min-h-screen flex flex-col">

        {{-- Top Header --}}
        <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4">
            <div class="flex items-center gap-4">
                {{-- Mobile sidebar toggle --}}
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="ri-menu-line text-xl text-gray-600"></i>
                </button>

                {{-- Breadcrumb / Page Title --}}
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                    @hasSection('breadcrumb')
                        <p class="text-xs text-gray-500 mt-0.5">@yield('breadcrumb')</p>
                    @endif
                </div>

                {{-- Header Actions --}}
                <div class="flex items-center gap-3">
                    @yield('header-actions')

                    {{-- Notifikasi stok --}}
                    @php
                        $totalAlert = \App\Models\StokProduk::whereColumn('jumlah_stok', '<', 'stok_minimum')->count()
                            + \App\Models\StokBahanBaku::whereColumn('jumlah_stok', '<', 'stok_minimum')->count();
                    @endphp
                    @if($totalAlert > 0)
                        <a href="{{ route('stok-produk.index') }}" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <i class="ri-notification-3-line text-xl text-gray-600"></i>
                            <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $totalAlert }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <div class="flex-1 p-6">

            {{-- Alert Flash Messages --}}
            @if(session('success'))
                <div id="flash-success" class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 animate-fade-in">
                    <i class="ri-checkbox-circle-line text-xl text-green-500"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-green-400 hover:text-green-600">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div id="flash-error" class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 animate-fade-in">
                    <i class="ri-error-warning-line text-xl text-red-500"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 animate-fade-in">
                    <i class="ri-error-warning-line text-xl text-red-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold mb-1">Terdapat kesalahan pada form:</p>
                        <ul class="text-sm list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- ========================
         GLOBAL SCRIPTS
         ======================== --}}
    <script>
        /**
         * Toggle sidebar untuk tampilan mobile.
         */
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('closed');
            overlay.classList.toggle('hidden');
        }

        /**
         * Buka modal dengan ID tertentu.
         * @param {string} modalId - ID elemen modal
         */
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        /**
         * Tutup modal dengan ID tertentu.
         * @param {string} modalId - ID elemen modal
         */
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        /**
         * Tutup modal saat klik di luar area modal.
         */
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeModal(e.target.id);
            }
        });

        /**
         * Auto-hide flash alert setelah 5 detik.
         */
        setTimeout(() => {
            ['flash-success', 'flash-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.transition = 'opacity 0.4s';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 400);
                }
            });
        }, 5000);

        /**
         * Konfirmasi aksi hapus sebelum submit form.
         * @param {Event} e - Form submit event
         * @param {string} name - Nama item yang akan dihapus
         */
        function confirmDelete(e, name) {
            if (!confirm(`Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
                e.preventDefault();
            }
        }

        /**
         * Format angka sebagai Rupiah.
         * @param {number} num - Angka yang akan diformat
         * @returns {string} String formatted sebagai Rupiah
         */
        function formatRupiah(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        }
    </script>

    @stack('scripts')
</body>
</html>
