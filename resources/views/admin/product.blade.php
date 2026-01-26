@extends('layouts.admin')

@section('title', 'Quản Lý Sản Phẩm | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Quản lý Sản phẩm')

@section('content')
<!-- Page Heading Section -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
    <div class="flex flex-col gap-1">
        <p class="text-slate-500 dark:text-slate-400 text-sm">Danh mục vật tư văn phòng phẩm toàn bệnh viện.</p>
    </div>
    <div class="flex gap-3">
        <button class="flex items-center justify-center gap-2 rounded-lg h-11 px-6 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
            <span class="material-symbols-outlined text-[20px]">file_download</span>
            Xuất báo cáo
        </button>
        <button class="flex items-center justify-center gap-2 rounded-lg h-11 px-6 bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-md shadow-primary/20 transition-all">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Thêm sản phẩm
        </button>
    </div>
</div>

<!-- Table Container -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Table Search & Filter Bar -->
    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-4">
        <div class="relative flex-1 max-w-lg">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                <span class="material-symbols-outlined text-[20px]">search</span>
            </span>
            <input class="block w-full h-11 pl-10 border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg text-sm focus:border-primary focus:ring-primary" placeholder="Tìm kiếm theo mã vật tư hoặc tên sản phẩm..." type="text" />
        </div>
        <button class="flex items-center gap-2 px-4 h-11 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            <span class="material-symbols-outlined text-[20px]">filter_list</span>
            Bộ lọc
        </button>
    </div>
    
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Mã vật tư</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tên sản phẩm</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Đơn vị tính</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Đơn giá</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tồn kho</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-5 text-sm font-medium text-primary">VT001</td>
                    <td class="px-6 py-5 text-sm font-semibold text-slate-900 dark:text-white">Giấy A4 Double A 80gsm</td>
                    <td class="px-6 py-5 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white">Ram</span>
                    </td>
                    <td class="px-6 py-5 text-sm text-slate-900 dark:text-slate-300 font-medium">85.000 ₫</td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-24 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: 75%;"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">150</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Chỉnh sửa">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50/50 rounded-lg transition-all" title="Xóa">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-5 text-sm font-medium text-primary">VT002</td>
                    <td class="px-6 py-5 text-sm font-semibold text-slate-900 dark:text-white">Bút bi Thiên Long TL-027</td>
                    <td class="px-6 py-5 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white">Hộp</span>
                    </td>
                    <td class="px-6 py-5 text-sm text-slate-900 dark:text-slate-300 font-medium">60.000 ₫</td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-24 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500 rounded-full" style="width: 22.5%;"></div>
                            </div>
                            <span class="text-sm font-bold text-yellow-600">45</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50/50 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-5 text-sm font-medium text-primary">VT003</td>
                    <td class="px-6 py-5 text-sm font-semibold text-slate-900 dark:text-white">Kẹp bướm ECHO 25mm</td>
                    <td class="px-6 py-5 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white">Hộp</span>
                    </td>
                    <td class="px-6 py-5 text-sm text-slate-900 dark:text-slate-300 font-medium">12.000 ₫</td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-24 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: 100%;"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">200</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50/50 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-5 text-sm font-medium text-primary">VT004</td>
                    <td class="px-6 py-5 text-sm font-semibold text-slate-900 dark:text-white">Bìa hồ sơ 100 lá Plus</td>
                    <td class="px-6 py-5 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white">Cái</span>
                    </td>
                    <td class="px-6 py-5 text-sm text-slate-900 dark:text-slate-300 font-medium">45.000 ₫</td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-24 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 rounded-full" style="width: 7.5%;"></div>
                            </div>
                            <span class="text-sm font-bold text-red-500">15</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50/50 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-5 text-sm font-medium text-primary">VT005</td>
                    <td class="px-6 py-5 text-sm font-semibold text-slate-900 dark:text-white">Sổ tay lò xo A5 Hải Tiến</td>
                    <td class="px-6 py-5 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white">Cuốn</span>
                    </td>
                    <td class="px-6 py-5 text-sm text-slate-900 dark:text-slate-300 font-medium">25.000 ₫</td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-24 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: 40%;"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">80</span>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-slate-500 hover:text-red-500 hover:bg-red-50/50 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <p class="text-sm text-slate-500">Hiển thị 1 - 5 trên 120 sản phẩm</p>
        <div class="flex gap-2">
            <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50" disabled>
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded-lg bg-primary text-white text-sm font-bold">1</button>
            <button class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">2</button>
            <button class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">3</button>
            <span class="px-2 self-center text-slate-500">...</span>
            <button class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">24</button>
            <button class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </button>
        </div>
    </div>
</div>
@endsection
