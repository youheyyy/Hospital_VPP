<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SuperAdmin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    @yield('styles')
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-6 border-b">
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
                <!-- 1. Dashboard -->
                <a href="{{ route('superadmin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('superadmin.dashboard') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-medium">Dashboard</span>
                </a>

                <!-- 2. Quản lý người dùng -->
                <a href="{{ route('superadmin.users') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('superadmin.users') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="material-symbols-outlined">group</span>
                    <span class="font-medium">Quản lý người dùng</span>
                </a>

                <!-- 3. Quản lý dữ liệu -->
                <a href="{{ route('superadmin.data-management') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('superadmin.data-management') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="material-symbols-outlined">database</span>
                    <span class="font-medium">Quản lý dữ liệu</span>
                </a>
            </nav>

            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2, 'UTF-8'), 'UTF-8') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit"
                            class="w-full text-xs text-gray-400 hover:text-red-600 font-medium transition-colors text-left px-2 py-1 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">logout</span>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>

</html>