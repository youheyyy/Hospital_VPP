<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', 'Hệ thống Quản lý Văn phòng phẩm')</title>

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />

    <!-- Material Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />

    <!-- Tailwind Config -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "sans": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>

    <!-- Custom Styles -->
    <style type="text/tailwindcss">
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 48;
            font-size: 28px;
        }
        .high-contrast-text {
            color: #000000;
        }
        .dark .high-contrast-text {
            color: #ffffff;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('styles')
</head>

<body class="bg-background-light dark:bg-background-dark text-[#0d121b] dark:text-white antialiased">
    <div class="flex h-screen flex-col overflow-hidden">
        <!-- Header -->
        <header
            class="flex items-center justify-between whitespace-nowrap border-b border-solid border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-gray-900 px-6 py-3 z-10">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-tmmc.png') }}" alt="HSS Logo" class="h-10 w-auto">
                    <div class="flex flex-col">
                        <h2 class="text-primary text-xl font-black leading-[0.8] tracking-tighter">TÂM TRÍ</h2>
                        <span class="text-[10px] font-bold text-slate-800 uppercase tracking-widest mt-1">CAO
                            LÃNH</span>
                        <span class="text-[9px] font-medium text-slate-500 leading-none mt-0.5">Bệnh viện đa khoa</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-1 justify-end gap-4 items-center">
                <!-- User Dropdown Menu -->
                <div style="position: relative;">
                    <button id="userDropdownButton" type="button" onclick="toggleUserDropdown(event)"
                        style="display: flex; align-items: center; gap: 8px; background: #f3f4f6; padding: 3px 12px 3px 3px; border-radius: 9999px; border: 1px solid #e5e7eb; cursor: pointer;">
                        <div
                            style="height: 32px; width: 32px; border-radius: 9999px; background: #135bec; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900;">
                            {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                        </div>
                        <span
                            style="font-weight: 700; font-size: 14px;">{{ auth()->user()->full_name ?? 'User' }}</span>
                        <span class="material-symbols-outlined"
                            style="font-size: 16px; color: #6b7280;">expand_more</span>
                    </button>

                    <!-- Dropdown -->
                    <div id="userDropdown"
                        style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 240px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; z-index: 1000;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                            <div style="font-weight: 600; font-size: 14px; color: #111827;">
                                {{ auth()->user()->full_name }}
                            </div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ auth()->user()->email }}
                            </div>
                        </div>

                        <a href="javascript:void(0)" onclick="showModal('personalInfoModal')"
                            style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; text-decoration: none; color: #374151; font-size: 14px;"
                            onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                            <span class="material-symbols-outlined" style="font-size: 18px;">person</span>
                            Thông tin cá nhân
                        </a>

                        <a href="javascript:void(0)" onclick="showModal('changePasswordModal')"
                            style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; text-decoration: none; color: #374151; font-size: 14px;"
                            onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
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

                @include('layouts.profile-modals')

                <script>
                    // Toggle dropdown function
                    function toggleUserDropdown(event) {
                        event.stopPropagation();
                        const dropdown = document.getElementById('userDropdown');
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
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <aside
                class="w-56 flex flex-col justify-between border-r border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                <div class="flex flex-col gap-6">
                    <div class="px-2 mb-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo-tmmc.png') }}" alt="Logo" class="h-12 w-auto">
                            <div class="flex flex-col text-left">
                                <h2 class="text-slate-900 text-base font-black leading-none tracking-tighter">TÂM TRÍ
                                </h2>
                                <span class="text-[9px] font-bold text-slate-800 uppercase tracking-widest mt-1">CAO
                                    LÃNH</span>
                                <span class="text-[8px] font-medium text-slate-500 leading-none mt-0.5 uppercase">Quản
                                    lý VPP</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('department.dashboard') ? 'bg-primary text-white font-black shadow-md' : 'text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 font-bold' }} text-sm transition-all"
                            href="{{ route('department.dashboard') }}">
                            <span class="material-symbols-outlined !text-xl">grid_view</span>
                            <span>Tổng quan</span>
                        </a>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('department.request.create') ? 'bg-primary text-white font-black shadow-md' : 'text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 font-bold' }} text-sm transition-all"
                            href="{{ route('department.request.create') }}">
                            <span class="material-symbols-outlined !text-xl">add_box</span>
                            <span>Tạo phiếu yêu cầu</span>
                        </a>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('department.list_request') ? 'bg-primary text-white font-black shadow-md' : 'text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 font-bold' }} text-sm transition-all"
                            href="{{ route('department.list_request') }}">
                            <span class="material-symbols-outlined !text-xl">list_alt</span>
                            <span>Danh sách yêu cầu</span>
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>

</html>