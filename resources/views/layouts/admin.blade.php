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
            <div class="flex flex-col items-center justify-center gap-3 w-full mb-8 pt-2">
                <img src="{{ asset('images/logo-tmmc.png') }}" alt="Logo" class="h-20 w-auto object-contain">
                <div class="flex items-center justify-center gap-1.5 w-full">
                    <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                    <span
                        class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.15em] whitespace-nowrap">Quản
                        Lý Văn Phòng Phẩm</span>
                    <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                </div>
            </div>
            <nav class="flex flex-col gap-1">
                @if(auth()->user()->role === 'SuperAdmin')
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('superadmin.users') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('superadmin.users') }}">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
                @else
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.dashboard') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.dashboard') }}">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    <span class="text-sm font-semibold">Tổng quan</span>
                </a>
                @endif

                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.consolidated') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.consolidated') }}">
                    <span class="material-symbols-outlined text-[20px]">assignment</span>
                    <span class="text-sm font-medium">Tổng hợp yêu cầu</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-md {{ request()->routeIs('admin.budgets.*') ? 'sidebar-item-active' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                    href="{{ route('admin.budgets.index') }}">
                    <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                    <span class="text-sm font-medium">Quản lý ngân sách</span>
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
                <div class="flex items-center gap-2">

                    <!-- User Dropdown Menu -->
                    <div style="position: relative;">
                        <button id="userDropdownButton" type="button" onclick="toggleUserDropdown(event)"
                            style="display: flex; align-items: center; gap: 8px; background: #f3f4f6; padding: 3px 12px 3px 3px; border-radius: 9999px; border: 1px solid #e5e7eb; cursor: pointer;">
                            <div
                                style="height: 32px; width: 32px; border-radius: 9999px; background: #135bec; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900;">
                                {{ mb_strtoupper(mb_substr(auth()->user()->full_name ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') }}
                            </div>
                            <span style="font-weight: 700; font-size: 14px; color: #111827;">{{
                                auth()->user()->full_name ?? 'User' }}</span>
                            <span class="material-symbols-outlined"
                                style="font-size: 16px; color: #6b7280;">expand_more</span>
                        </button>

                        <!-- Dropdown -->
                        <div id="userDropdown"
                            style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 240px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; z-index: 1000;">
                            <div style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                                <div style="font-weight: 600; font-size: 14px; color: #111827; text-align: left;">
                                    {{ auth()->user()->full_name }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px; text-align: left;">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>

                            <a href="javascript:void(0)" onclick="showModal('personalInfoModal')"
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; text-decoration: none; color: #374151; font-size: 14px; text-align: left;"
                                onmouseover="this.style.background='#f3f4f6'"
                                onmouseout="this.style.background='white'">
                                <span class="material-symbols-outlined" style="font-size: 18px;">person</span>
                                Thông tin cá nhân
                            </a>

                            <a href="javascript:void(0)" onclick="showModal('changePasswordModal')"
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; text-decoration: none; color: #374151; font-size: 14px; text-align: left;"
                                onmouseover="this.style.background='#f3f4f6'"
                                onmouseout="this.style.background='white'">
                                <span class="material-symbols-outlined" style="font-size: 18px;">lock</span>
                                Đổi mật khẩu
                            </a>

                            <div style="border-top: 1px solid #e5e7eb; margin: 4px 0;"></div>

                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit"
                                    style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: none; border: none; color: #dc2626; font-size: 14px; font-weight: 600; cursor: pointer; border-radius: 0 0 12px 12px; text-align: left;"
                                    onmouseover="this.style.background='#fef2f2'"
                                    onmouseout="this.style.background='transparent'">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <script>
            // Toggle dropdown function
            function toggleUserDropdown(event) {
                event.stopPropagation();
                const dropdown = document.getElementById('userDropdown');
                if (!dropdown) return;
                const isVisible = dropdown.style.display === 'block';
                dropdown.style.display = isVisible ? 'none' : 'block';
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function (event) {
                const dropdown = document.getElementById('userDropdown');
                const button = document.getElementById('userDropdownButton');

                // Check if click is outside both dropdown and button
                if (dropdown && button &&
                    !dropdown.contains(event.target) &&
                    !button.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });
        </script>
        <div class="p-8 max-w-[1600px] w-full mx-auto">
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700 transition-colors"><span
                        class="material-symbols-outlined">close</span></button>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" x-show="show"
                class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-red-500 hover:text-red-700 transition-colors"><span
                        class="material-symbols-outlined">close</span></button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>