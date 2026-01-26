@extends('layouts.admin')

@section('title', 'Quản Lý Khoa/Phòng | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Quản lý Khoa/Phòng')

@section('content')
<!-- Breadcrumbs -->
<div class="flex flex-wrap gap-2 mb-4">
    <a class="text-gray-500 dark:text-gray-400 text-sm font-medium leading-normal hover:text-primary" href="#">Trang chủ</a>
    <span class="text-gray-500 text-sm font-medium leading-normal">/</span>
    <a class="text-gray-500 dark:text-gray-400 text-sm font-medium leading-normal hover:text-primary" href="#">Quản trị</a>
    <span class="text-gray-500 text-sm font-medium leading-normal">/</span>
    <span class="text-primary text-sm font-bold leading-normal">Quản lý Khoa/Phòng</span>
</div>

<!-- Page Heading & CTA -->
<div class="flex flex-wrap justify-between items-end gap-3 mb-6">
    <div class="flex min-w-72 flex-col gap-2">
        <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Quản lý danh sách các khoa và phòng ban trong toàn bệnh viện.</p>
    </div>
    <button class="flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-6 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
        <span class="material-symbols-outlined text-[20px]">add</span>
        <span class="truncate">Thêm Khoa/Phòng</span>
    </button>
</div>

<!-- Search and Filter Bar -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 mb-6 shadow-sm">
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <label class="flex flex-col min-w-40 h-12 w-full">
                <div class="flex w-full flex-1 items-stretch rounded-lg h-full">
                    <div class="text-gray-400 flex border-none bg-slate-100 dark:bg-slate-800 items-center justify-center pl-4 rounded-l-lg">
                        <span class="material-symbols-outlined text-[22px]">search</span>
                    </div>
                    <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-r-lg text-slate-900 dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border-none bg-slate-100 dark:bg-slate-800 h-full placeholder:text-gray-400 px-4 pl-2 text-sm font-normal leading-normal" placeholder="Tìm kiếm theo mã, tên khoa hoặc trưởng khoa..." />
                </div>
            </label>
        </div>
        <div class="flex gap-3">
            <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50">
                <span class="material-symbols-outlined text-[18px]">filter_list</span>
                <span>Bộ lọc</span>
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Xuất file</span>
            </button>
        </div>
    </div>
</div>

<!-- Table Container -->
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-6 py-4 text-slate-900 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">Mã Khoa/Phòng</th>
                    <th class="px-6 py-4 text-slate-900 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">Tên Khoa/Phòng</th>
                    <th class="px-6 py-4 text-slate-900 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">Trưởng Khoa/Phòng</th>
                    <th class="px-6 py-4 text-slate-900 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-4 text-right text-slate-900 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <!-- Row 1 -->
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-slate-600 dark:text-slate-400">K-NOI</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">Khoa Nội Tổng Hợp</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-primary text-xs font-bold">NA</div>
                        BS. Nguyễn Văn A
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Đang hoạt động</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-slate-600 dark:text-slate-400">K-NGOAI</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">Khoa Ngoại Chấn Thương</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-purple-600 text-xs font-bold">TB</div>
                        BS. Trần Thị B
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Đang hoạt động</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-slate-600 dark:text-slate-400">P-TCKT</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">Phòng Tài Chính Kế Toán</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center text-orange-600 text-xs font-bold">LC</div>
                        ThS. Lê Văn C
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Ngưng hoạt động</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 4 -->
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-slate-600 dark:text-slate-400">K-DUOC</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">Khoa Dược</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 text-xs font-bold">PD</div>
                        DS. Phạm Minh D
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Đang hoạt động</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Row 5 -->
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-slate-600 dark:text-slate-400">P-HCTH</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">Phòng Hành Chính Tổng Hợp</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-pink-100 dark:bg-pink-900/40 flex items-center justify-center text-pink-600 text-xs font-bold">HE</div>
                        Bà Hoàng Thị E
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Đang hoạt động</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
        <p class="text-sm text-slate-500 dark:text-slate-400">Hiển thị 1 - 5 của 24 khoa/phòng</p>
        <div class="flex items-center gap-2">
            <button class="flex items-center justify-center w-8 h-8 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-400 hover:text-primary disabled:opacity-50" disabled>
                <span class="material-symbols-outlined text-[18px]">chevron_left</span>
            </button>
            <button class="flex items-center justify-center w-8 h-8 rounded bg-primary text-white font-medium text-sm">1</button>
            <button class="flex items-center justify-center w-8 h-8 rounded border border-transparent hover:border-slate-200 dark:hover:border-slate-700 text-slate-600 dark:text-slate-400 text-sm">2</button>
            <button class="flex items-center justify-center w-8 h-8 rounded border border-transparent hover:border-slate-200 dark:hover:border-slate-700 text-slate-600 dark:text-slate-400 text-sm">3</button>
            <span class="text-gray-400">...</span>
            <button class="flex items-center justify-center w-8 h-8 rounded border border-transparent hover:border-slate-200 dark:hover:border-slate-700 text-slate-600 dark:text-slate-400 text-sm">5</button>
            <button class="flex items-center justify-center w-8 h-8 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-400 hover:text-primary">
                <span class="material-symbols-outlined text-[18px]">chevron_right</span>
            </button>
        </div>
    </div>
</div>
@endsection
