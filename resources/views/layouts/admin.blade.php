<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin - Quản Lý Văn Phòng Phẩm')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#1e1b4b',
                        },
                        mint: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            400: '#94a3b8',
                            500: '#64748b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ["Plus Jakarta Sans", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body { @apply bg-slate-50 text-slate-900 antialiased font-sans; }
        }
        .sidebar-item {
            @apply flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200 cursor-pointer;
        }
        .sidebar-item.active {
            @apply bg-indigo-600 text-white shadow-lg shadow-indigo-200;
        }
        .sidebar-item .material-symbols-outlined {
            @apply text-2xl;
        }
        .sidebar-item.active:hover {
            @apply text-white;
        }
    </style>
    @stack('styles')
</head>

<body class="flex h-screen overflow-hidden @yield('body-class')" @yield('body-attrs')>
    {{-- ===== SIDEBAR ===== --}}
    <aside class="w-64 bg-white border-r border-slate-100 flex flex-col flex-shrink-0 transition-all duration-300">
        {{-- Logo --}}
        <div class="p-6 border-b border-slate-100">
            <div class="flex flex-col items-center justify-center gap-3 w-full pt-2">
                <img src="{{ asset('images/logo-tmmc.png') }}" alt="Logo" class="h-20 w-auto object-contain">
                <div class="flex items-center justify-center gap-1.5 w-full">
                    <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.15em] whitespace-nowrap">Quản Lý Văn Phòng Phẩm</span>
                    <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 p-4 space-y-2">
            <a class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}">
                <span class="material-symbols-outlined">grid_view</span>
                <span class="text-sm font-bold">Tổng quan</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('admin.consolidated') ? 'active' : '' }}"
                href="{{ route('admin.consolidated') }}">
                <span class="material-symbols-outlined">assignment</span>
                <span class="text-sm font-bold">Tổng hợp yêu cầu</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"
                href="{{ route('admin.budgets.index') }}">
                <span class="material-symbols-outlined">account_balance_wallet</span>
                <span class="text-sm font-bold">Quản lý ngân sách</span>
            </a>
        </nav>

        {{-- User Info --}}
        <div class="p-4 border-t border-slate-100">
            <div class="bg-slate-50 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shadow-sm">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name ?? auth()->user()->full_name ?? 'U', 0, 2, 'UTF-8'), 'UTF-8') }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name ?? auth()->user()->full_name ?? 'User' }}</p>
                        <p class="text-[10px] text-slate-400 uppercase truncate">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="w-full text-xs font-bold text-slate-500 hover:text-indigo-600 text-left px-2 py-1 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN ===== --}}
    <main class="flex-1 overflow-y-auto flex flex-col">
        {{-- Sticky Header --}}
        <header class="h-20 px-10 flex justify-between items-center bg-white/50 backdrop-blur-md sticky top-0 z-30 border-b border-slate-100 flex-shrink-0">
            @yield('header-content')
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="mx-10 mt-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-500 hover:text-green-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show"
            class="mx-10 mt-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined">error</span>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-500 hover:text-red-700 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        @endif

        {{-- Page Content --}}
        <div class="@yield('content-class', 'p-10 max-w-[1600px] mx-auto w-full')">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>