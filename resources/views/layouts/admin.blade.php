<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Hệ Thống Vật Tư')</title>
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
                        primary: '#4f46e5',
                        indigo: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#1e1b4b',
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
    </style>
    @stack('styles')
</head>

<body class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-white border-r border-slate-100 flex flex-col no-print transition-all duration-300 shadow-sm">
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
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <span class="material-symbols-outlined">grid_view</span>
                <span class="text-sm font-bold">Tổng quan</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('admin.consolidated*') ? 'active' : '' }}" href="{{ route('admin.consolidated') }}">
                <span class="material-symbols-outlined">assignment</span>
                <span class="text-sm font-bold">Tổng hợp yêu cầu</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('admin.products') ? 'active' : '' }}" href="{{ route('admin.products') }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="text-sm font-bold">Quản lý sản phẩm VPP</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-100">
            <div class="bg-slate-50 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shadow-sm">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2, 'UTF-8'), 'UTF-8') }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-400 uppercase truncate">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-xs font-bold text-slate-500 hover:text-indigo-600 text-left px-2 py-1 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex justify-between items-center gap-3 no-print min-w-0 h-20">
            <div class="flex-shrink-0">
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">@yield('page-title', 'Bảng Điều Khiển')</h1>
                <p class="text-xs text-slate-400 font-medium">@yield('page-subtitle', now()->format('d/m/Y H:i'))</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black text-xs">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                    </div>
                    <span class="text-sm font-bold text-slate-700">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
                <span class="material-symbols-outlined">error</span>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>
   </main>

    @stack('scripts')
</body>

</html>