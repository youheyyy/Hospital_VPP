<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Hệ thống Quản lý Văn phòng phẩm')</title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    
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
        <header class="flex items-center justify-between whitespace-nowrap border-b-2 border-solid border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-gray-900 px-8 py-5 z-10">
            <div class="flex items-center gap-10">
                <div class="flex items-center gap-4 text-primary">
                    <div class="size-12 flex items-center justify-center bg-primary rounded-xl text-white">
                        <span class="material-symbols-outlined !text-3xl">local_hospital</span>
                    </div>
                    <h2 class="text-[#0d121b] dark:text-white text-2xl font-black leading-tight tracking-tight">HSS QUẢN LÝ</h2>
                </div>
                <nav class="hidden md:flex items-center gap-8">
                    <a class="text-primary text-lg font-bold border-b-4 border-primary pb-1" href="{{ route('department.dashboard') }}">Tổng quan</a>
                    <a class="text-[#4c669a] dark:text-gray-400 text-lg font-bold hover:text-primary transition-colors" href="#">Yêu cầu của tôi</a>
                    <a class="text-[#4c669a] dark:text-gray-400 text-lg font-bold hover:text-primary transition-colors" href="#">Thông báo</a>
                </nav>
            </div>
            <div class="flex flex-1 justify-end gap-6 items-center">
                <div class="relative w-80">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#4c669a] !text-2xl">search</span>
                    <input class="w-full h-14 pl-12 pr-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-[#f8f9fc] dark:bg-gray-800 text-lg font-medium focus:ring-4 focus:ring-primary/20" placeholder="Tìm kiếm phiếu, vật tư..."/>
                </div>
                <div class="flex gap-3">
                    <button class="flex items-center justify-center rounded-xl h-14 w-14 bg-[#e7ebf3] dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                </div>
                <!-- User Dropdown Menu -->
                <div style="position: relative;">
                    <button id="userDropdownButton" type="button"
                            onclick="toggleUserDropdown(event)" 
                            style="display: flex; align-items: center; gap: 12px; background: #f3f4f6; padding: 4px 16px 4px 4px; border-radius: 9999px; border: 1px solid #e5e7eb; cursor: pointer;">
                        <div style="height: 48px; width: 48px; border-radius: 9999px; background: #135bec; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900;">
                            {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                        </div>
                        <span style="font-weight: 700; font-size: 18px;">{{ auth()->user()->full_name ?? 'User' }}</span>
                        <span class="material-symbols-outlined" style="font-size: 20px; color: #6b7280;">expand_more</span>
                    </button>
                    
                    <!-- Dropdown -->
                    <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 240px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; z-index: 1000;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                            <div style="font-weight: 600; font-size: 14px; color: #111827;">{{ auth()->user()->full_name }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ auth()->user()->email }}</div>
                        </div>
                        
                        <a href="#" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; text-decoration: none; color: #374151; font-size: 14px;" 
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                            <span class="material-symbols-outlined" style="font-size: 18px;">person</span>
                            Thông tin cá nhân
                        </a>
                        
                        <a href="#" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; text-decoration: none; color: #374151; font-size: 14px;" 
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                            <span class="material-symbols-outlined" style="font-size: 18px;">settings</span>
                            Cài đặt
                        </a>
                        
                        <div style="border-top: 1px solid #e5e7eb; margin: 4px 0;"></div>
                        
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: none; border: none; color: #dc2626; font-size: 14px; font-weight: 600; cursor: pointer; border-radius: 0 0 12px 12px; text-align: left;" 
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
                
                <script>
                    // Toggle dropdown function
                    function toggleUserDropdown(event) {
                        event.stopPropagation();
                        const dropdown = document.getElementById('userDropdown');
                        const isVisible = dropdown.style.display === 'block';
                        dropdown.style.display = isVisible ? 'none' : 'block';
                    }
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(event) {
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
            <aside class="w-72 flex flex-col justify-between border-r-2 border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col px-2 gap-1">
                        <h1 class="text-black dark:text-white text-xl font-black leading-normal uppercase">Khoa Tim Mạch</h1>
                        <p class="text-primary font-bold text-sm bg-primary/10 px-3 py-1 rounded-md inline-block self-start">Khu A • Tầng 2</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('department.dashboard') ? 'bg-primary text-white font-black shadow-md' : 'text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 font-bold' }} text-lg transition-all" href="{{ route('department.dashboard') }}">
                            <span class="material-symbols-outlined">grid_view</span>
                            <span>Tổng quan</span>
                        </a>
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('department.request') ? 'bg-primary text-white font-black shadow-md' : 'text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 font-bold' }} text-lg transition-all" href="{{ route('department.request') }}">
                            <span class="material-symbols-outlined">add_box</span>
                            <span>Tạo phiếu yêu cầu</span>
                        </a>
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 transition-all font-bold text-lg" href="#">
                            <span class="material-symbols-outlined">list_alt</span>
                            <span>Danh sách yêu cầu</span>
                        </a>
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 transition-all font-bold text-lg" href="#">
                            <span class="material-symbols-outlined">history</span>
                            <span>Lịch sử</span>
                        </a>
                    </div>
                </div>
                <div class="pt-6 border-t-2 border-[#e7ebf3] dark:border-gray-800">
                    <a href="{{ route('department.request') }}" class="w-full flex items-center justify-center gap-3 rounded-2xl h-16 px-6 bg-primary text-white text-xl font-black shadow-xl shadow-primary/30 hover:bg-blue-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined !text-3xl">post_add</span>
                        <span>TẠO PHIẾU MỚI</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-10">
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
