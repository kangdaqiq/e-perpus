<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | E-Perpus Standalone</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#6366f1', // Indigo
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Style kustom untuk kelancaran UI -->
    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased font-sans transition-colors duration-300"
      x-data="{ 
          darkMode: localStorage.getItem('dark-mode') === 'true',
          sidebarOpen: window.innerWidth >= 1024,
          init() {
              this.$watch('darkMode', val => {
                  localStorage.setItem('dark-mode', val);
                  document.documentElement.classList.toggle('dark', val);
              });
              document.documentElement.classList.toggle('dark', this.darkMode);
          }
      }">

    <!-- Page Preloader -->
    <div id="loader" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900 transition-opacity duration-300" 
         x-data="{ show: true }" x-show="show" x-init="window.addEventListener('load', () => { setTimeout(() => show = false, 300) })">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-indigo-200 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-indigo-600 rounded-full animate-spin border-t-transparent"></div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Menu -->
        <aside class="w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex-shrink-0 transition-all duration-300"
               :class="sidebarOpen ? 'translate-x-0 block' : '-translate-x-full hidden lg:block'"
               x-cloak>
            <!-- Logo Section -->
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('perpus.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/30 text-white">
                        <i class="fa-solid fa-book-open text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight bg-gradient-to-r from-indigo-600 to-purple-500 bg-clip-text text-transparent">E-Perpus</h1>
                        <p class="text-xs text-slate-400 font-medium">Integrated System</p>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1">
                <a href="{{ route('perpus.dashboard') }}" 
                   class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('perpus.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-gauge-high text-lg"></i>
                    <span>Dashboard</span>
                </a>

                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Modul Pustaka</div>

                <a href="{{ route('perpus.buku.index') }}" 
                   class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('perpus.buku.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-book text-lg"></i>
                    <span>Katalog Buku</span>
                </a>

                <a href="{{ route('perpus.kunjungan.index') }}" 
                   class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('perpus.kunjungan.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-users-rectangle text-lg"></i>
                    <span>Buku Tamu</span>
                </a>

                <a href="{{ route('perpus.loan.index') }}" 
                   class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('perpus.loan.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-handshake text-lg"></i>
                    <span>Peminjaman Buku</span>
                </a>

                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Perangkat & Data</div>

                <a href="{{ route('perpus.device.index') }}" 
                   class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('perpus.device.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-server text-lg"></i>
                    <span>Scanner RFID</span>
                </a>

                <a href="{{ route('perpus.member.index') }}" 
                   class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all duration-200 {{ request()->routeIs('perpus.member.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                    <span>Siswa & Guru</span>
                </a>

                <div class="pt-4">
                    <form action="{{ route('perpus.sync') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center gap-4 px-4 py-3 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                            <i class="fa-solid fa-arrows-rotate text-lg text-emerald-500"></i>
                            <span>Sinkron Data Absen</span>
                        </button>
                    </form>
                </div>
            </nav>

            <!-- User Profile & Logout -->
            <div class="mt-auto p-4 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3 p-2">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                        {{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs text-slate-400 capitalize truncate">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all duration-200">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Nav -->
            <header class="h-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <span class="text-xs font-semibold px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-full">
                        {{ auth()->user()->school->name ?? 'Semua Sekolah' }}
                    </span>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <button @click="darkMode = !darkMode" class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                        <i class="fa-solid" :class="darkMode ? 'fa-sun text-lg' : 'fa-moon text-lg'"></i>
                    </button>
                </div>
            </header>

            <!-- Main Content Grid -->
            <main class="flex-1 p-6 md:p-8 overflow-y-auto">
                <!-- Alert Handling -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center gap-3 text-emerald-700 dark:text-emerald-400">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-2xl flex items-center gap-3 text-rose-700 dark:text-rose-400">
                        <i class="fa-solid fa-circle-xmark text-xl"></i>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
