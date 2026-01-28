@extends('layouts.department')

@section('title', 'Tổng quan Khoa/Phòng')

@section('styles')
<style type="text/tailwindcss">
    .glass-card {
        @apply bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-white/40 dark:border-slate-800/40 shadow-xl shadow-slate-200/50 dark:shadow-none;
    }
    .status-badge-glow {
        box-shadow: 0 0 15px currentColor;
    }
    .sparkline {
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto w-full space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ Auth::user()->department->name ?? 'Khoa Ngoại Tổng Quát' }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium mt-0.5 text-sm">
                Hệ thống quản lý vật tư & văn phòng phẩm
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button class="glass-card flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold hover:bg-white transition-all">
                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                Tháng {{ date('m') }}, {{ date('Y') }}
            </button>
            <button class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold shadow-lg">
                <span class="material-symbols-outlined text-[16px]">export_notes</span>
                Báo cáo nhanh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1: Đang chờ -->
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div class="p-2 rounded-xl bg-gradient-to-tr from-orange-400 to-red-500 shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white !text-xl">pending_actions</span>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang chờ</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">05</div>
                </div>
            </div>
            <div class="h-10 w-full">
                <svg class="w-full h-full" viewBox="0 0 100 20">
                    <path class="sparkline stroke-orange-500" d="M0,15 L10,12 L20,18 L30,14 L40,16 L50,10 L60,12 L70,8 L80,10 L90,5 L100,7" fill="none"></path>
                </svg>
            </div>
            <p class="text-[10px] font-semibold text-orange-600 dark:text-orange-400 mt-3 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px]">warning</span>
                Cần phê duyệt gấp 2 phiếu
            </p>
        </div>

        <!-- Card 2: Đã duyệt -->
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div class="p-2 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-500 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white !text-xl">check_circle</span>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đã duyệt</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">128</div>
                </div>
            </div>
            <div class="h-10 w-full">
                <svg class="w-full h-full" viewBox="0 0 100 20">
                    <path class="sparkline stroke-emerald-500" d="M0,18 L10,15 L20,10 L30,12 L40,8 L50,6 L60,10 L70,4 L80,6 L90,2 L100,5" fill="none"></path>
                </svg>
            </div>
            <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 mt-3 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px]">trending_up</span>
                +15% so với tháng trước
            </p>
        </div>

        <!-- Card 3: Tổng yêu cầu -->
        <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div class="p-2 rounded-xl bg-gradient-to-tr from-blue-400 to-indigo-500 shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white !text-xl">description</span>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng yêu cầu</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">342</div>
                </div>
            </div>
            <div class="h-10 w-full">
                <svg class="w-full h-full" viewBox="0 0 100 20">
                    <path class="sparkline stroke-blue-500" d="M0,10 L20,10 L40,10 L60,8 L80,5 L100,2" fill="none"></path>
                </svg>
            </div>
            <p class="text-[10px] font-semibold text-slate-500 mt-3">Năm {{ date('Y') }} (YTD)</p>
        </div>

        <!-- Card 4: Nhu cầu vật tư -->
        <div class="glass-card p-4 rounded-2xl group">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Nhu cầu vật tư</h3>
                <span class="material-symbols-outlined text-purple-500 !text-lg">auto_graph</span>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-[11px] font-bold mb-1">
                        <span class="text-slate-700 dark:text-slate-300">Giấy A4 Double A</span>
                        <span class="text-purple-600">85%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full" style="width: 85%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-[11px] font-bold mb-1">
                        <span class="text-slate-700 dark:text-slate-300">Bút bi Thiên Long</span>
                        <span class="text-blue-600">62%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full" style="width: 62%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requests Table -->
    <div class="glass-card rounded-2xl overflow-hidden border-none shadow-xl shadow-slate-200/40">
        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Phiếu yêu cầu gần đây</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Theo dõi tiến độ xử lý văn phòng phẩm theo thời gian thực</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-base group-focus-within:text-blue-500">filter_list</span>
                    <select class="pl-8 pr-8 py-1.5 text-xs font-bold bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none">
                        <option>Tất cả trạng thái</option>
                        <option>Đã duyệt</option>
                        <option>Chờ duyệt</option>
                        <option>Đang xử lý</option>
                    </select>
                </div>
                <button class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 text-xs font-bold px-4 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                    Bộ lọc nâng cao
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30 text-slate-400 text-[10px] font-black uppercase tracking-[0.12em]">
                        <th class="px-5 py-3">Mã phiếu</th>
                        <th class="px-5 py-3">Thời gian</th>
                        <th class="px-5 py-3">Nội dung</th>
                        <th class="px-5 py-3">Người tạo</th>
                        <th class="px-5 py-3 text-center">Trạng thái</th>
                        <th class="px-5 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/5 transition-all group">
                        <td class="px-5 py-3">
                            <span class="font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-lg text-xs">YC-9912</span>
                        </td>
                        <td class="px-5 py-3 text-xs font-semibold text-slate-500">Hôm nay, 10:45</td>
                        <td class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-200">Cấp phát VPP tháng {{ date('m') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-[9px] flex items-center justify-center font-bold">A</div>
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Nguyễn Văn A</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 status-badge-glow ring-1 ring-emerald-500/5">
                                <span class="w-1 h-1 rounded-full bg-emerald-500 mr-1.5 shadow-[0_0_4px_rgba(16,185,129,1)]"></span>
                                ĐÃ DUYỆT
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-white dark:hover:bg-slate-800 rounded-lg transition-all shadow-sm">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/5 transition-all group">
                        <td class="px-5 py-3">
                            <span class="font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-lg text-xs">YC-9910</span>
                        </td>
                        <td class="px-5 py-3 text-xs font-semibold text-slate-500">Hôm qua, 14:20</td>
                        <td class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-200">Bổ sung giấy in khẩn cấp</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-[9px] flex items-center justify-center font-bold">B</div>
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Trần Thị B</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 status-badge-glow ring-1 ring-orange-500/5">
                                <span class="w-1 h-1 rounded-full bg-orange-500 mr-1.5 shadow-[0_0_4px_rgba(245,158,11,1)]"></span>
                                CHỜ DUYỆT
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-white dark:hover:bg-slate-800 rounded-lg transition-all shadow-sm">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/5 transition-all group">
                        <td class="px-5 py-3">
                            <span class="font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-lg text-xs">YC-9908</span>
                        </td>
                        <td class="px-5 py-3 text-xs font-semibold text-slate-500">09/10, 08:30</td>
                        <td class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-200">VPP định kỳ quý 4</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-[9px] flex items-center justify-center font-bold">C</div>
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Lê Văn C</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 status-badge-glow ring-1 ring-blue-500/5">
                                <span class="w-1 h-1 rounded-full bg-blue-500 mr-1.5 shadow-[0_0_4px_rgba(59,130,246,1)]"></span>
                                ĐANG XỬ LÝ
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-white dark:hover:bg-slate-800 rounded-lg transition-all shadow-sm">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-5 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/50">
            <p class="text-[10px] font-bold text-slate-400">Hiển thị <span class="text-slate-900 dark:text-white">10</span> trong số <span class="text-slate-900 dark:text-white">128</span> yêu cầu</p>
            <div class="flex gap-1.5">
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined text-base">chevron_left</span>
                </button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-600 text-white font-bold shadow-md shadow-blue-500/20 text-xs">1</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 transition-colors font-bold text-xs">2</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined text-base">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection