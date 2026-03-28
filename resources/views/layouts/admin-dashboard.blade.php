<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Quản trị VPP')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
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
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            font-family: "Plus Jakarta Sans", sans-serif;
        }
        .bento-card {
            background-color: white;
            border-radius: 2rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 40px -15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            transition: all 0.3s;
        }
        .bento-card:hover {
            box-shadow: 0 20px 50px -12px rgba(0,0,0,0.08);
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            color: #64748b;
            transition: all 0.2s;
            cursor: pointer;
        }
        .sidebar-item:hover {
            color: #4f46e5;
            background-color: #f5f7ff;
        }
        .sidebar-item.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }
        .sidebar-item .material-symbols-outlined {
            font-size: 1.5rem;
        }
        .progress-ring {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .horizontal-bar {
            height: 2rem;
            background-color: #ebf0fe;
            border-radius: 0.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.5s;
        }
        .horizontal-bar-fill {
            height: 100%;
            background-color: #4f46e5;
            border-radius: 0.5rem;
            transition: all 0.7s;
        }
    </style>
    @yield('styles')
</head>

<body class="flex h-screen overflow-hidden">
    <aside class="w-64 bg-white border-r border-slate-100 flex flex-col flex-shrink-0 transition-all duration-300">
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
        <nav class="flex-1 p-4 space-y-2">
            <a class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <span class="material-symbols-outlined">grid_view</span>
                <span class="text-sm font-bold">Tổng quan</span>
            </a>
            <a class="sidebar-item {{ request()->routeIs('admin.consolidated') ? 'active' : '' }}" href="{{ route('admin.consolidated') }}">
                <span class="material-symbols-outlined">assignment</span>
                <span class="text-sm font-bold">Tổng hợp yêu cầu</span>
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
    <main class="flex-1 overflow-y-auto">
        <header class="h-20 px-10 flex justify-between items-center bg-white/50 backdrop-blur-md sticky top-0 z-30">
            @yield('header')
        </header>
        <div class="p-10 max-w-[1600px] mx-auto">
            @yield('content')
        </div>
    </main>

    @yield('scripts')
</body>

</html>
