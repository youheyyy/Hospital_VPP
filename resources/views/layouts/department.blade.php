<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống Quản lý Văn phòng phẩm')</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["Plus Jakarta Sans", "sans-serif"],
                    },
                },
            },
        };
    </script>

    <style type="text/tailwindcss">
        @layer base {
            body { @apply bg-gray-50 text-gray-900 antialiased font-sans; }
        }
        .sidebar-item {
            @apply flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 cursor-pointer text-sm font-semibold;
        }
        .sidebar-item.active {
            @apply bg-blue-600 text-white shadow-lg shadow-blue-200;
        }
        .sidebar-item.active:hover {
            @apply text-white;
        }
        .sidebar-item .material-symbols-outlined {
            @apply text-xl;
        }
        .excel-table { border-collapse: collapse; width: 100%; }
        .excel-table th, .excel-table td { border: 1px solid #d1d5db; padding: 8px 12px; }
        .excel-table th { background: #f3f4f6; font-weight: 600; text-align: center; }
        .category-header { background: #3b82f6 !important; color: white; font-weight: bold; text-align: left; }
        .product-row.hidden { display: none; }
        .total-row { background: #fef3c7; font-weight: bold; }
    </style>

    @stack('styles')
</head>

<body class="flex h-screen overflow-hidden">
    {{-- ===== SIDEBAR ===== --}}
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col flex-shrink-0">
        {{-- Logo --}}
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col items-center justify-center gap-3 w-full pt-2">
                <img src="{{ asset('images/logo-tmmc.png') }}" class="h-20 w-auto object-contain" alt="Logo">
                <div class="flex items-center justify-center gap-1.5 w-full">
                    <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em] whitespace-nowrap">Quản Lý Văn Phòng Phẩm</span>
                    <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('department.index') }}"
                class="sidebar-item {{ request()->routeIs('department.index') ? 'active' : '' }}">
                <span class="material-symbols-outlined">assignment</span>
                <span>Yêu cầu VPP</span>
            </a>
            <a href="{{ route('department.history') }}"
                class="sidebar-item {{ request()->routeIs('department.history') ? 'active' : '' }}">
                <span class="material-symbols-outlined">history</span>
                <span>Lịch sử yêu cầu</span>
            </a>
        </nav>

        {{-- User Info --}}
        <div class="p-4 border-t border-gray-100">
            <div class="bg-gray-50 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        {{ mb_strtoupper(mb_substr($department->name ?? 'D', 0, 2, 'UTF-8'), 'UTF-8') }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $department->name ?? 'Department' }}</p>
                        <p class="text-[10px] text-gray-400 truncate">{{ auth()->user()->name }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="w-full text-xs font-bold text-gray-500 hover:text-blue-600 text-left px-2 py-1 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN ===== --}}
    <main class="flex-1 flex flex-col overflow-hidden">
        {{-- Sticky Header --}}
        <header class="bg-white border-b border-gray-100 px-8 py-4 flex-shrink-0">
            @yield('header-content')
        </header>

        {{-- Page Content --}}
        <div class="flex-1 overflow-y-auto p-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">error</span>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>