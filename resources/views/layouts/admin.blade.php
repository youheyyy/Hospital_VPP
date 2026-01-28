<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin - Hệ Thống Vật Tư Y Tế')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "sans": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body {
                @apply font-sans text-slate-900 bg-background-light;
            }
        }
        .sidebar-item-active {
            @apply bg-primary/10 text-primary border-r-4 border-primary;
        }
        .chart-bar {
            @apply rounded-t-sm transition-all duration-300;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>

<body class="dark:bg-background-dark dark:text-slate-100 min-h-screen flex">
    <aside
        class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col h-screen sticky top-0">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/logo-tmmc.png') }}" alt="Hospital Logo" class="h-12 w-auto">
                <div>
                    <h1 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white uppercase">VẬT TƯ Y TẾ</h1>
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Hệ Thống Quản Trị</p>
                </div>
            </div>
            <nav class="flex flex-col gap-1">
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.dashboard') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.dashboard') }}">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    <span class="text-sm font-semibold">Tổng quan</span>
                </a>

                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.requests.index') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.requests.index') }}">
                    <span class="material-symbols-outlined text-[20px]">description</span>
                    <span class="text-sm font-medium">Phiếu yêu cầu</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.approve_summary_votes') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.approve_summary_votes') }}">
                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    <span class="text-sm font-medium">Duyệt phiếu tổng hợp</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.orders.*') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.orders.index') }}">
                    <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                    <span class="text-sm font-medium">Danh sách PO</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.product') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.product') }}">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                    <span class="text-sm font-medium">Danh mục vật tư</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.report') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.report') }}">
                    <span class="material-symbols-outlined text-[20px]">bar_chart</span>
                    <span class="text-sm font-medium">Báo cáo thống kê</span>
                </a>
            </nav>
        </div>
        </div>
    </aside>
    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header
            class="h-14 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-8 flex items-center justify-between sticky top-0 z-20">
            <h2 class="text-base font-bold text-slate-800 dark:text-white">@yield('page-title', 'Bảng Điều Khiển')</h2>
            <div class="flex items-center gap-4">
                <div class="relative w-72">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input
                        class="w-full pl-9 pr-4 py-1.5 bg-slate-100 dark:bg-slate-800 border-none rounded-md focus:ring-1 focus:ring-primary text-xs"
                        placeholder="Tìm kiếm nhanh..." type="text" />
                </div>
                <div class="flex items-center gap-2 border-l border-slate-200 dark:border-slate-700 ml-2 pl-4">
                    <button class="relative p-1.5 text-slate-500 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-xl">notifications</span>
                        <span
                            class="absolute top-1 right-1 w-2 h-2 bg-red-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                            <div class="size-7 rounded-full bg-cover bg-center ring-2 ring-primary/20"
                                style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDwW7nsZi-I9duwko3cgQ1S6rVqpQymVRRN6AR8lIU7086B5y3d1SXJffjIToNIGTDWXsUi4pFTTGljjBMIvCY1sSwF8cAqd9tLwM0FgmKV49Q_X6MpxED7AsTe38BGh2VKbZavAZTYHMciAsSiWpPQbyKCIrNbxiBOYFEgrEcXRVubs06SPUFLTYCwutVprnNUdN7-Dxhv1tCZwwywNHsM5L6_PBSxtFf0Bezki4Uhwank13ymjqlQ__a7bWEHgrF4YAmyCbu-gE_7')">
                            </div>
                            <span
                                class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ Auth::user()->name ?? 'Admin' }}</span>
                            <span class="material-symbols-outlined text-sm text-slate-500">expand_more</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50"
                            style="display: none;">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ Auth::user()->name ?? 'Admin Hiếu' }}
                                </p>
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ Auth::user()->email ??
                                    'admin@hospital.com' }}</p>
                            </div>
                            <a href="#"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <span
                                    class="material-symbols-outlined text-sm text-slate-600 dark:text-slate-400">person</span>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Thông tin cá
                                    nhân</span>
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <span
                                    class="material-symbols-outlined text-sm text-slate-600 dark:text-slate-400">settings</span>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Cài đặt</span>
                            </a>
                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors text-red-500">
                                <span class="material-symbols-outlined text-sm">logout</span>
                                <span class="text-xs font-bold">Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                    <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </header>
        <div class="p-8 max-w-[1600px] w-full mx-auto">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>