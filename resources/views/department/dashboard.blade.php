<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Tổng quan Khoa/Phòng - Hệ thống Quản lý Văn phòng phẩm</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
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
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
</head>

<body class="bg-background-light dark:bg-background-dark text-[#0d121b] dark:text-white antialiased">
    <div class="flex h-screen flex-col overflow-hidden">
        <!-- Header -->
        <header
            class="flex items-center justify-between whitespace-nowrap border-b-2 border-solid border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-gray-900 px-8 py-5 z-10">
            <div class="flex items-center gap-10">
                <div class="flex items-center gap-4 text-primary">
                    <div class="size-12 flex items-center justify-center bg-primary rounded-xl text-white">
                        <span class="material-symbols-outlined !text-3xl">local_hospital</span>
                    </div>
                    <h2 class="text-[#0d121b] dark:text-white text-2xl font-black leading-tight tracking-tight">HSS QUẢN
                        LÝ</h2>
                </div>
                <nav class="hidden md:flex items-center gap-8">
                    <a class="text-primary text-lg font-bold border-b-4 border-primary pb-1"
                        href="{{ route('department.dashboard') }}">Tổng quan</a>
                    <a class="text-[#4c669a] dark:text-gray-400 text-lg font-bold hover:text-primary transition-colors"
                        href="{{ route('department.list_request') }}">Yêu cầu của tôi</a>
                    <a class="text-[#4c669a] dark:text-gray-400 text-lg font-bold hover:text-primary transition-colors"
                        href="#">Thông báo</a>
                </nav>
            </div>
            <div class="flex flex-1 justify-end gap-6 items-center">
                <div class="relative w-80">
                    <span
                        class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#4c669a] !text-2xl">search</span>
                    <input
                        class="w-full h-14 pl-12 pr-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-[#f8f9fc] dark:bg-gray-800 text-lg font-medium focus:ring-4 focus:ring-primary/20"
                        placeholder="Tìm kiếm phiếu, vật tư..." />
                </div>
                <div class="flex gap-3">
                    <button
                        class="flex items-center justify-center rounded-xl h-14 w-14 bg-[#e7ebf3] dark:bg-gray-800 text-[#0d121b] dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                </div>

                <!-- Dropdown User -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-3 bg-gray-100 dark:bg-gray-800 p-1 pr-4 rounded-full border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <div
                            class="h-12 w-12 rounded-full bg-primary text-white flex items-center justify-center text-xl font-black">
                            {{ substr(Auth::user()->fullname ?? 'K', 0, 1) }}
                        </div>
                        <span
                            class="font-bold text-lg hidden lg:block">{{ Auth::user()->fullname ?? 'Khoa Phòng' }}</span>
                        <span class="material-symbols-outlined text-gray-500">expand_more</span>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg py-1 border border-gray-200 dark:border-gray-700 z-50"
                        style="display: none;">
                        <a href="#"
                            class="block px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            Hồ sơ
                        </a>
                        <a href="#"
                            class="block px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            Cài đặt
                        </a>
                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <aside
                class="w-72 flex flex-col justify-between border-r-2 border-[#e7ebf3] dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col px-2 gap-1">
                        <h1 class="text-black dark:text-white text-xl font-black leading-normal uppercase">
                            {{ Auth::user()->department->name ?? 'Khoa Phòng' }}
                        </h1>
                        <p
                            class="text-primary font-bold text-sm bg-primary/10 px-3 py-1 rounded-md inline-block self-start">
                            {{ Auth::user()->department->location ?? 'Khu vực chính' }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl bg-primary text-white font-black text-lg shadow-md"
                            href="{{ route('department.dashboard') }}">
                            <span class="material-symbols-outlined">grid_view</span>
                            <span>Tổng quan</span>
                        </a>
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 transition-all font-bold text-lg"
                            href="{{ route('department.request.create') }}">
                            <span class="material-symbols-outlined">add_box</span>
                            <span>Tạo phiếu yêu cầu</span>
                        </a>
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 transition-all font-bold text-lg"
                            href="{{ route('department.list_request') }}">
                            <span class="material-symbols-outlined">list_alt</span>
                            <span>Danh sách yêu cầu</span>
                        </a>
                        <a class="flex items-center gap-4 px-4 py-4 rounded-xl text-[#4c669a] dark:text-gray-400 hover:bg-[#e7ebf3] dark:hover:bg-gray-800 transition-all font-bold text-lg"
                            href="#">
                            <span class="material-symbols-outlined">history</span>
                            <span>Lịch sử</span>
                        </a>
                    </div>
                </div>
                <div class="pt-6 border-t-2 border-[#e7ebf3] dark:border-gray-800">
                    <a href="{{ route('department.request.create') }}"
                        class="w-full flex items-center justify-center gap-3 rounded-2xl h-16 px-6 bg-primary text-white text-xl font-black shadow-xl shadow-primary/30 hover:bg-blue-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined !text-3xl">post_add</span>
                        <span>TẠO PHIẾU MỚI</span>
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-10">
                <div class="max-w-7xl mx-auto flex flex-col gap-10">
                    <!-- Header Section -->
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <div class="flex flex-col gap-2">
                            <h2 class="text-black dark:text-white text-4xl font-black tracking-tight">Tổng quan
                                Khoa/Phòng</h2>
                            <p class="text-[#4c669a] dark:text-gray-400 text-xl font-medium">Theo dõi tình trạng cấp
                                phát văn phòng phẩm</p>
                        </div>
                        <div class="flex gap-4">
                            <button
                                class="flex items-center gap-3 px-6 py-3.5 rounded-xl border-2 border-[#cfd7e7] dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white text-lg font-black hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                                <span class="material-symbols-outlined">print</span>
                                <span>In báo cáo</span>
                            </button>
                            <button
                                class="flex items-center gap-3 px-6 py-3.5 rounded-xl bg-primary text-white text-lg font-black hover:bg-blue-700 transition-colors shadow-lg shadow-primary/20">
                                <span class="material-symbols-outlined">sync</span>
                                <span>Cập nhật</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <a href="{{ route('department.request.create') }}"
                            class="bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all hover:scale-105 active:scale-95 block text-center">
                            <div class="flex flex-col items-center gap-3">
                                <span class="material-symbols-outlined !text-5xl">post_add</span>
                                <span class="text-xl font-black">Tạo yêu cầu mới</span>
                            </div>
                        </a>
                        <button
                            class="bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all hover:scale-105 active:scale-95">
                            <div class="flex flex-col items-center gap-3">
                                <span class="material-symbols-outlined !text-5xl">inventory_2</span>
                                <span class="text-xl font-black">Kho VPP</span>
                            </div>
                        </button>
                        <a href="{{ route('department.list_request') }}"
                            class="bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all hover:scale-105 active:scale-95 block text-center">
                            <div class="flex flex-col items-center gap-3">
                                <span class="material-symbols-outlined !text-5xl">history</span>
                                <span class="text-xl font-black">Lịch sử</span>
                            </div>
                        </a>
                        <button
                            class="bg-gradient-to-br from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all hover:scale-105 active:scale-95">
                            <div class="flex flex-col items-center gap-3">
                                <span class="material-symbols-outlined !text-5xl">bar_chart</span>
                                <span class="text-xl font-black">Báo cáo</span>
                            </div>
                        </button>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        <div
                            class="bg-white dark:bg-gray-900 flex flex-col gap-4 rounded-2xl p-6 border-2 border-[#cfd7e7] dark:border-gray-800 shadow-md hover:shadow-xl transition-shadow">
                            <div class="flex justify-between items-start">
                                <p
                                    class="text-[#4c669a] dark:text-gray-400 text-base font-black uppercase tracking-wider">
                                    Phiếu đang chờ</p>
                                <span class="material-symbols-outlined text-amber-500 !text-4xl">hourglass_empty</span>
                            </div>
                            <div class="flex items-end gap-3">
                                <p class="text-black dark:text-white text-5xl font-black leading-none">05</p>
                                <span
                                    class="bg-amber-100 text-amber-800 text-xs font-black px-3 py-1 rounded-lg mb-1 tracking-tighter">CẦN
                                    XỬ LÝ</span>
                            </div>
                            <p
                                class="text-[#4c669a] dark:text-gray-500 text-sm font-bold border-t pt-3 border-gray-100 dark:border-gray-800">
                                2 phiếu chờ Trưởng khoa duyệt</p>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-900 flex flex-col gap-4 rounded-2xl p-6 border-2 border-[#cfd7e7] dark:border-gray-800 shadow-md hover:shadow-xl transition-shadow">
                            <div class="flex justify-between items-start">
                                <p
                                    class="text-[#4c669a] dark:text-gray-400 text-base font-black uppercase tracking-wider">
                                    Đã duyệt tháng này</p>
                                <span class="material-symbols-outlined text-green-600 !text-4xl">verified</span>
                            </div>
                            <div class="flex items-end gap-3">
                                <p class="text-black dark:text-white text-5xl font-black leading-none">128</p>
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-black px-3 py-1 rounded-lg mb-1">+15%</span>
                            </div>
                            <p
                                class="text-[#4c669a] dark:text-gray-500 text-sm font-bold border-t pt-3 border-gray-100 dark:border-gray-800">
                                Tổng giá trị: 4.250.000đ</p>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-900 flex flex-col gap-4 rounded-2xl p-6 border-2 border-[#cfd7e7] dark:border-gray-800 shadow-md hover:shadow-xl transition-shadow">
                            <div class="flex justify-between items-start">
                                <p
                                    class="text-[#4c669a] dark:text-gray-400 text-base font-black uppercase tracking-wider">
                                    Tổng yêu cầu</p>
                                <span class="material-symbols-outlined text-blue-600 !text-4xl">description</span>
                            </div>
                            <div class="flex items-end gap-3">
                                <p class="text-black dark:text-white text-5xl font-black leading-none">342</p>
                                <span class="bg-blue-100 text-blue-800 text-xs font-black px-3 py-1 rounded-lg mb-1">Tất
                                    cả</span>
                            </div>
                            <p
                                class="text-[#4c669a] dark:text-gray-500 text-sm font-bold border-t pt-3 border-gray-100 dark:border-gray-800">
                                Từ đầu năm đến nay</p>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-900 flex flex-col gap-4 rounded-2xl p-6 border-2 border-[#cfd7e7] dark:border-gray-800 shadow-md hover:shadow-xl transition-shadow">
                            <div class="flex justify-between items-start">
                                <p
                                    class="text-[#4c669a] dark:text-gray-400 text-base font-black uppercase tracking-wider">
                                    VPP dùng nhiều nhất</p>
                                <span class="material-symbols-outlined text-primary !text-4xl">trending_up</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="material-symbols-outlined text-primary !text-2xl">description</span>
                                <p class="text-black dark:text-white text-lg font-black leading-tight">Giấy A4 Double A
                                </p>
                            </div>
                            <p
                                class="text-[#4c669a] dark:text-gray-500 text-sm font-bold border-t pt-3 border-gray-100 dark:border-gray-800">
                                Đã dùng: 24 Ream / Tháng</p>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <!-- Recent Requests - Takes 2 columns -->
                        <div class="xl:col-span-2 flex flex-col gap-6">
                            <div class="flex items-center justify-between px-2">
                                <h3 class="text-black dark:text-white text-2xl font-black">Phiếu yêu cầu gần đây</h3>
                                <a class="text-primary text-lg font-black hover:underline flex items-center gap-1"
                                    href="{{ route('department.list_request') }}">
                                    Xem tất cả <span class="material-symbols-outlined !text-xl">chevron_right</span>
                                </a>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-900 border-2 border-[#cfd7e7] dark:border-gray-800 rounded-2xl overflow-hidden shadow-lg">
                                <table class="w-full text-left border-collapse">
                                    <thead
                                        class="bg-gray-100 dark:bg-gray-800 border-b-2 border-[#e7ebf3] dark:border-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-4 text-black dark:text-gray-300 text-sm font-black uppercase">
                                                Mã phiếu</th>
                                            <th
                                                class="px-6 py-4 text-black dark:text-gray-300 text-sm font-black uppercase">
                                                Nội dung</th>
                                            <th
                                                class="px-6 py-4 text-black dark:text-gray-300 text-sm font-black uppercase text-center">
                                                Trạng thái</th>
                                            <th
                                                class="px-6 py-4 text-black dark:text-gray-300 text-sm font-black uppercase text-center">
                                                Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-6 py-5 text-base font-black">#YC-9912</td>
                                            <td class="px-6 py-5">
                                                <p class="text-base font-black text-black dark:text-white">Giấy In A4,
                                                    Bút bi xanh</p>
                                                <p class="text-xs font-bold text-[#4c669a]">Ngày tạo: 10:30 Hôm nay</p>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-100 text-blue-800 text-xs font-black uppercase tracking-tight">
                                                    Đang xử lý
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <button class="text-primary hover:text-blue-700 font-black text-sm">Chi
                                                    tiết</button>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-6 py-5 text-base font-black">#YC-9884</td>
                                            <td class="px-6 py-5">
                                                <p class="text-base font-black text-black dark:text-white">Sổ hội chẩn,
                                                    Bìa hồ sơ</p>
                                                <p class="text-xs font-bold text-[#4c669a]">Ngày tạo: Hôm qua</p>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 text-xs font-black uppercase tracking-tight">
                                                    Chờ duyệt
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <button class="text-primary hover:text-blue-700 font-black text-sm">Chi
                                                    tiết</button>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-6 py-5 text-base font-black">#YC-9750</td>
                                            <td class="px-6 py-5">
                                                <p class="text-base font-black text-black dark:text-white">Mực in HP
                                                    107a</p>
                                                <p class="text-xs font-bold text-[#4c669a]">Ngày tạo: 2 ngày trước</p>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-100 text-green-800 text-xs font-black uppercase tracking-tight">
                                                    Đã nhận
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <button class="text-primary hover:text-blue-700 font-black text-sm">Chi
                                                    tiết</button>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-6 py-5 text-base font-black">#YC-9701</td>
                                            <td class="px-6 py-5">
                                                <p class="text-base font-black text-black dark:text-white">Bút dạ quang,
                                                    Kẹp tài liệu</p>
                                                <p class="text-xs font-bold text-[#4c669a]">Ngày tạo: 3 ngày trước</p>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-100 text-green-800 text-xs font-black uppercase tracking-tight">
                                                    Đã nhận
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <button class="text-primary hover:text-blue-700 font-black text-sm">Chi
                                                    tiết</button>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                            <td class="px-6 py-5 text-base font-black">#YC-9652</td>
                                            <td class="px-6 py-5">
                                                <p class="text-base font-black text-black dark:text-white">Băng keo
                                                    trong, Kéo văn phòng</p>
                                                <p class="text-xs font-bold text-[#4c669a]">Ngày tạo: 5 ngày trước</p>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-100 text-red-800 text-xs font-black uppercase tracking-tight">
                                                    Từ chối
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <button class="text-primary hover:text-blue-700 font-black text-sm">Chi
                                                    tiết</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right Sidebar - Takes 1 column -->
                        <div class="flex flex-col gap-8">
                            <!-- Notifications -->
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between px-2">
                                    <h3 class="text-black dark:text-white text-xl font-black">Thông báo mới</h3>
                                    <span
                                        class="bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-full">3</span>
                                </div>
                                <div
                                    class="bg-white dark:bg-gray-900 border-2 border-[#cfd7e7] dark:border-gray-800 rounded-2xl p-6 shadow-lg flex flex-col gap-4">
                                    <div
                                        class="flex gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border-l-4 border-blue-500">
                                        <span class="material-symbols-outlined text-blue-600 !text-2xl">info</span>
                                        <div class="flex-1">
                                            <p class="text-sm font-black text-black dark:text-white">Phiếu #YC-9912 đã
                                                được duyệt</p>
                                            <p class="text-xs font-bold text-[#4c669a] mt-1">5 phút trước</p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border-l-4 border-amber-500">
                                        <span class="material-symbols-outlined text-amber-600 !text-2xl">warning</span>
                                        <div class="flex-1">
                                            <p class="text-sm font-black text-black dark:text-white">Yêu cầu bổ sung
                                                thông tin</p>
                                            <p class="text-xs font-bold text-[#4c669a] mt-1">1 giờ trước</p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border-l-4 border-green-500">
                                        <span
                                            class="material-symbols-outlined text-green-600 !text-2xl">check_circle</span>
                                        <div class="flex-1">
                                            <p class="text-sm font-black text-black dark:text-white">VPP đã được cấp
                                                phát</p>
                                            <p class="text-xs font-bold text-[#4c669a] mt-1">2 giờ trước</p>
                                        </div>
                                    </div>
                                    <a href="#"
                                        class="text-primary text-sm font-black hover:underline text-center pt-2 border-t border-gray-100 dark:border-gray-800">
                                        Xem tất cả thông báo
                                    </a>
                                </div>
                            </div>

                            <!-- Usage Chart -->
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between px-2">
                                    <h3 class="text-black dark:text-white text-xl font-black">Mức sử dụng</h3>
                                    <span
                                        class="text-primary bg-primary/10 px-3 py-1 rounded-full text-xs font-black uppercase">Tháng
                                        này</span>
                                </div>
                                <div
                                    class="bg-white dark:bg-gray-900 border-2 border-[#cfd7e7] dark:border-gray-800 rounded-2xl p-6 shadow-lg flex flex-col gap-6">
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-black text-black dark:text-white">Giấy A4</span>
                                            <span class="text-sm font-black text-primary">85%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3">
                                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full shadow-inner"
                                                style="width: 85%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-black text-black dark:text-white">Bút bi</span>
                                            <span class="text-sm font-black text-green-600">62%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3">
                                            <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full shadow-inner"
                                                style="width: 62%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-black text-black dark:text-white">Bìa hồ sơ</span>
                                            <span class="text-sm font-black text-amber-600">45%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3">
                                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-3 rounded-full shadow-inner"
                                                style="width: 45%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-black text-black dark:text-white">Sổ tay</span>
                                            <span class="text-sm font-black text-purple-600">28%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3">
                                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full shadow-inner"
                                                style="width: 28%"></div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="material-symbols-outlined text-primary !text-2xl">lightbulb</span>
                                            <div>
                                                <h4 class="text-primary text-sm font-black mb-1">Gợi ý</h4>
                                                <p
                                                    class="text-blue-900 dark:text-blue-200 text-xs font-bold leading-snug">
                                                    Mức sử dụng Giấy A4 cao hơn 10% so với định mức.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Info & Quick Links -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-gradient-to-br from-primary to-blue-700 rounded-2xl p-8 shadow-xl text-white">
                            <h3 class="text-2xl font-black mb-6">Thông tin khoa/phòng</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <span class="material-symbols-outlined !text-3xl">badge</span>
                                    <div>
                                        <p class="text-sm font-bold opacity-90">Trưởng khoa</p>
                                        <p class="text-lg font-black">BS. Nguyễn Văn A</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="material-symbols-outlined !text-3xl">people</span>
                                    <div>
                                        <p class="text-sm font-bold opacity-90">Số nhân viên</p>
                                        <p class="text-lg font-black">45 người</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="material-symbols-outlined !text-3xl">location_on</span>
                                    <div>
                                        <p class="text-sm font-bold opacity-90">Vị trí</p>
                                        <p class="text-lg font-black">Khu A - Tầng 2</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="material-symbols-outlined !text-3xl">phone</span>
                                    <div>
                                        <p class="text-sm font-bold opacity-90">Liên hệ</p>
                                        <p class="text-lg font-black">Ext: 2345</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-900 border-2 border-[#cfd7e7] dark:border-gray-800 rounded-2xl p-8 shadow-lg">
                            <h3 class="text-2xl font-black mb-6 text-black dark:text-white">Liên kết nhanh</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="#"
                                    class="flex flex-col items-center gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors border-2 border-blue-100 dark:border-blue-800">
                                    <span class="material-symbols-outlined text-primary !text-4xl">description</span>
                                    <span class="text-sm font-black text-black dark:text-white text-center">Mẫu
                                        phiếu</span>
                                </a>
                                <a href="#"
                                    class="flex flex-col items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors border-2 border-green-100 dark:border-green-800">
                                    <span class="material-symbols-outlined text-green-600 !text-4xl">help</span>
                                    <span class="text-sm font-black text-black dark:text-white text-center">Hướng
                                        dẫn</span>
                                </a>
                                <a href="#"
                                    class="flex flex-col items-center gap-3 p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors border-2 border-purple-100 dark:border-purple-800">
                                    <span
                                        class="material-symbols-outlined text-purple-600 !text-4xl">contact_support</span>
                                    <span class="text-sm font-black text-black dark:text-white text-center">Hỗ
                                        trợ</span>
                                </a>
                                <a href="#"
                                    class="flex flex-col items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors border-2 border-amber-100 dark:border-amber-800">
                                    <span class="material-symbols-outlined text-amber-600 !text-4xl">settings</span>
                                    <span class="text-sm font-black text-black dark:text-white text-center">Cài
                                        đặt</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <footer class="mt-8 text-center pb-8">
                        <p class="text-[#4c669a] dark:text-gray-500 text-base font-bold uppercase tracking-widest">
                            HỆ THỐNG QUẢN LÝ VĂN PHÒNG PHẨM BỆNH VIỆN v2.5
                        </p>
                        <p class="text-[#4c669a] dark:text-gray-500 text-sm font-medium mt-1">© 2023 Khoa Tim Mạch - Hub
                            Quản lý</p>
                    </footer>
                </div>
            </main>
        </div>
    </div>

</body>

</html>