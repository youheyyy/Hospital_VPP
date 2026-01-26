@extends('layouts.admin')

@section('title', 'Admin - Tổng Quan | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Bảng Điều Khiển')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">Chào buổi sáng, Admin</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Hệ thống đang hoạt động ổn định. Bạn có <span
            class="text-primary font-semibold">12 thông báo</span> cần xử lý trong hôm nay.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div
        class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group cursor-pointer">
        <div>
            <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mb-2">Phiếu chờ duyệt</p>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">45</span>
                <span
                    class="text-xs font-medium text-amber-500 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-md">Cần
                    xử lý</span>
            </div>
        </div>
        <div class="bg-primary/10 p-4 rounded-xl text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
            <span class="material-symbols-outlined text-3xl">pending_actions</span>
        </div>
    </div>
    <div
        class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group cursor-pointer">
        <div>
            <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mb-2">Tổng sản phẩm trong
                kho</p>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">1,200</span>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-md">Đã cập nhật</span>
            </div>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/10 p-4 rounded-xl text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
            <span class="material-symbols-outlined text-3xl">inventory</span>
        </div>
    </div>
    <div
        class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group cursor-pointer">
        <div>
            <p class="text-[13px] font-semibold text-slate-500 dark:text-slate-400 mb-2">Khoa/Phòng hoạt
                động</p>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">28</span>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-md">Trực tuyến</span>
            </div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-xl text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-all duration-300">
            <span class="material-symbols-outlined text-3xl">local_hospital</span>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div
        class="lg:col-span-3 bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Biểu đồ yêu cầu hàng tháng</h3>
                <p class="text-xs text-slate-500 mt-1">Dữ liệu thống kê năm 2024 (Đơn vị: Phiếu)</p>
            </div>
            <div class="flex p-1 bg-slate-100 dark:bg-slate-700 rounded-md">
                <button
                    class="px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300">Tháng</button>
                <button
                    class="px-3 py-1 text-xs font-semibold bg-white dark:bg-slate-600 shadow-sm rounded text-primary">Năm</button>
            </div>
        </div>
        <div
            class="h-64 flex items-end justify-between gap-6 px-4 border-b border-slate-100 dark:border-slate-700 pb-2">
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar"
                    style="height: 40%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T4</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar"
                    style="height: 60%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T5</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar"
                    style="height: 50%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T6</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/40 hover:bg-primary/60 rounded-t-sm chart-bar border-x-2 border-t-2 border-primary/20"
                    style="height: 80%;"></div>
                <span class="text-[11px] font-bold text-primary uppercase">T7</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm chart-bar"
                    style="height: 70%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T8</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/60 rounded-t-sm chart-bar" style="height: 95%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T9</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/20 rounded-t-sm chart-bar" style="height: 55%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T10</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-3">
                <div class="w-full bg-primary/20 rounded-t-sm chart-bar" style="height: 45%;"></div>
                <span class="text-[11px] font-medium text-slate-400 uppercase">T11</span>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Xử lý
                nhanh</h3>
            <button class="text-primary font-semibold text-[11px] hover:underline">Xem tất cả</button>
        </div>
        <div class="space-y-4 flex-1">
            <div
                class="flex gap-3 items-start pb-4 border-b border-slate-50 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-900/50 p-1 -mx-1 rounded-lg transition-colors cursor-pointer">
                <div
                    class="size-8 rounded bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">priority_high</span>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight truncate">
                        Khoa Nội: 50 ram giấy A4</p>
                    <p class="text-[10px] text-slate-500 font-medium mt-1">15 phút trước • Chờ duyệt</p>
                </div>
            </div>
            <div
                class="flex gap-3 items-start pb-4 border-b border-slate-50 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-900/50 p-1 -mx-1 rounded-lg transition-colors cursor-pointer">
                <div
                    class="size-8 rounded bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">warning</span>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight truncate">
                        Hết mực in Canon LBP</p>
                    <p class="text-[10px] text-slate-500 font-medium mt-1">1 giờ trước • Cần nhập kho</p>
                </div>
            </div>
            <div
                class="flex gap-3 items-start pb-4 border-b border-slate-50 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-900/50 p-1 -mx-1 rounded-lg transition-colors cursor-pointer">
                <div
                    class="size-8 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">done</span>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight truncate">
                        Khoa Ngoại: Đã nhận viết</p>
                    <p class="text-[10px] text-slate-500 font-medium mt-1">2 giờ trước • Hoàn tất</p>
                </div>
            </div>
        </div>
        <button
            class="w-full mt-6 py-2.5 bg-primary text-white rounded-lg font-bold text-xs hover:bg-blue-700 transition-colors shadow-sm shadow-primary/20">TẠO
            PHIẾU MỚI</button>
    </div>
</div>
<div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
    <div
        class="md:col-span-3 bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-full">
                <span class="material-symbols-outlined text-slate-500">info</span>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-300">Cần hỗ trợ về hệ thống? Liên hệ phòng
                CNTT: <span class="font-bold">Ext 112</span></p>
        </div>
        <button
            class="px-4 py-1.5 text-xs font-bold border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50">Hướng
            dẫn sử dụng</button>
    </div>
</div>
@endsection
