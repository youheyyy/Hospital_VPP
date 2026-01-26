@extends('layouts.admin')

@section('title', 'Duyệt Phiếu Tổng Hợp | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Duyệt Phiếu Tổng Hợp')

@push('styles')
<style type="text/tailwindcss">
    :root {
        --primary: #0d9488;
        --primary-hover: #0f766e;
        --bg-main: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar {
        height: 4px;
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .tab-active {
        @apply border-b-2 border-[var(--primary)] text-[var(--primary)] font-bold;
    }
</style>
@endpush

@section('content')
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-6 -mx-8 -mt-8">
    <div class="px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-4">
            <div class="bg-[var(--primary)] p-2.5 rounded-xl shadow-sm">
                <span class="material-symbols-outlined text-white text-2xl">receipt_long</span>
            </div>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-800 dark:text-white">Duyệt Phiếu Tổng Hợp</h1>
                <p class="text-xs font-medium text-slate-500 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">apartment</span>
                    Hệ thống quản lý vật tư bệnh viện
                </p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-lg">
                <button class="flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-700 text-[var(--primary)] font-bold rounded-md shadow-sm border border-slate-200 dark:border-slate-600 hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-[20px]">print</span>
                    <span class="text-sm">In phiếu (20 khoa)</span>
                </button>
                <button class="flex items-center gap-2 px-5 py-2.5 bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white font-bold rounded-md shadow-md transition-all ml-1">
                    <span class="material-symbols-outlined text-[20px]">task_alt</span>
                    <span class="text-sm">Duyệt tổng hợp</span>
                </button>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <div class="px-6 overflow-x-auto custom-scrollbar">
            <div class="flex items-center gap-1 whitespace-nowrap">
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent" href="#">BẢNG TỔNG</a>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent" href="#">TỔNG HỢP</a>
                <div class="w-px h-4 bg-slate-200 dark:bg-slate-700 mx-2"></div>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent uppercase" href="#">CĐHA</a>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent uppercase" href="#">Xét nghiệm</a>
                <a class="px-5 py-4 text-xs tab-active uppercase" href="#">Cấp cứu</a>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent uppercase" href="#">Phòng mổ</a>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent uppercase" href="#">Khoa dược</a>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent uppercase" href="#">Phòng khám</a>
                <a class="px-5 py-4 text-xs font-bold text-slate-500 hover:text-[var(--primary)] transition-colors border-b-2 border-transparent uppercase" href="#">Nội nhi</a>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <nav aria-label="Breadcrumb" class="flex text-[10px] text-slate-400 mb-2 uppercase tracking-widest font-bold">
            <ol class="flex items-center space-x-2">
                <li class="hover:text-slate-600 transition-colors cursor-pointer">VẬT TƯ</li>
                <li class="material-symbols-outlined text-[12px]">chevron_right</li>
                <li class="text-[var(--primary)]">KHOA CẤP CỨU</li>
            </ol>
        </nav>
        <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-3">
            Chi tiết phiếu: Khoa Cấp cứu
            <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-bold rounded uppercase tracking-wider border border-amber-200 dark:border-amber-800">Đang chờ</span>
        </h2>
    </div>
    <div class="flex items-center gap-3">
        <div class="flex items-center bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400 mr-2 uppercase tracking-tight">Kỳ báo cáo:</span>
            <select class="bg-transparent border-none text-sm font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 cursor-pointer">
                <option>Tháng 10/2023</option>
                <option>Tháng 11/2023</option>
            </select>
        </div>
        <div class="flex items-center bg-white dark:bg-slate-800 rounded-xl px-4 py-2.5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="material-symbols-outlined text-[var(--primary)] mr-2">filter_alt</span>
            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Lọc nhanh</span>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden mb-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[1100px]">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-16">STT</th>
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Tên hàng hóa / Quy cách</th>
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-28">ĐVT</th>
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-28">Số lượng</th>
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right w-40">Đơn giá (VNĐ)</th>
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right w-44">Thành tiền</th>
                    <th class="px-6 py-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-24">Tác vụ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                    <td class="px-6 py-4" colspan="7">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-[var(--primary)] rounded-full"></div>
                            <span class="text-xs font-black text-[var(--primary)] uppercase tracking-wider">VĂN PHÒNG PHẨM - NHÀ SÁCH THANH VÂN</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-5 text-sm text-center text-slate-400 font-medium">01</td>
                    <td class="px-6 py-5 text-sm font-bold text-slate-700 dark:text-slate-200">Bìa sơ mi lá lỗ cung tròn</td>
                    <td class="px-6 py-5 text-sm text-center font-medium text-slate-600 dark:text-slate-400">Xấp</td>
                    <td class="px-6 py-5 text-sm text-center font-black text-red-500 bg-red-50 dark:bg-red-900/10">1</td>
                    <td class="px-6 py-5 text-sm text-right font-bold text-slate-600 dark:text-slate-400 font-mono">25.000</td>
                    <td class="px-6 py-5 text-sm text-right font-black text-slate-800 dark:text-white font-mono">25.000</td>
                    <td class="px-6 py-5 text-center">
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-[var(--primary)] hover:bg-[var(--primary)]/10 transition-all">
                            <span class="material-symbols-outlined text-[20px]">edit_square</span>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-5 text-sm text-center text-slate-400 font-medium">17</td>
                    <td class="px-6 py-5 text-sm font-bold text-slate-700 dark:text-slate-200">Kim bấm số 10</td>
                    <td class="px-6 py-5 text-sm text-center font-medium text-slate-600 dark:text-slate-400">Hộp</td>
                    <td class="px-6 py-5 text-sm text-center font-black text-red-500 bg-red-50 dark:bg-red-900/10">2</td>
                    <td class="px-6 py-5 text-sm text-right font-bold text-slate-600 dark:text-slate-400 font-mono">2.800</td>
                    <td class="px-6 py-5 text-sm text-right font-black text-slate-800 dark:text-white font-mono">5.600</td>
                    <td class="px-6 py-5 text-center">
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-[var(--primary)] hover:bg-[var(--primary)]/10 transition-all">
                            <span class="material-symbols-outlined text-[20px]">edit_square</span>
                        </button>
                    </td>
                </tr>
                <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                    <td class="px-6 py-4" colspan="7">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-[var(--primary)] rounded-full"></div>
                            <span class="text-xs font-black text-[var(--primary)] uppercase tracking-wider">VẬT TƯ TIÊU HAO - NHÀ SÁCH QUỐC NAM</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-5 text-sm text-center text-slate-400 font-medium">171</td>
                    <td class="px-6 py-5 text-sm font-bold text-slate-700 dark:text-slate-200">Ly nhựa trung 380ml</td>
                    <td class="px-6 py-5 text-sm text-center font-medium text-slate-600 dark:text-slate-400">Cây</td>
                    <td class="px-6 py-5 text-sm text-center font-black text-red-500 bg-red-50 dark:bg-red-900/10">1</td>
                    <td class="px-6 py-5 text-sm text-right font-bold text-slate-600 dark:text-slate-400 font-mono">13.000</td>
                    <td class="px-6 py-5 text-sm text-right font-black text-slate-800 dark:text-white font-mono">13.000</td>
                    <td class="px-6 py-5 text-center">
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-[var(--primary)] hover:bg-[var(--primary)]/10 transition-all">
                            <span class="material-symbols-outlined text-[20px]">edit_square</span>
                        </button>
                    </td>
                </tr>
                <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                    <td class="px-6 py-4" colspan="7">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-[var(--primary)] rounded-full"></div>
                            <span class="text-xs font-black text-[var(--primary)] uppercase tracking-wider">VẬT TƯ - HÓA CHẤT VỆ SINH</span>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-5 text-sm text-center text-slate-400 font-medium">237</td>
                    <td class="px-6 py-5 text-sm font-bold text-slate-700 dark:text-slate-200">Khăn sữa cotton cao cấp</td>
                    <td class="px-6 py-5 text-sm text-center font-medium text-slate-600 dark:text-slate-400">Hộp</td>
                    <td class="px-6 py-5 text-sm text-center font-black text-red-500 bg-red-50 dark:bg-red-900/10">20</td>
                    <td class="px-6 py-5 text-sm text-right font-bold text-slate-600 dark:text-slate-400 font-mono">3.000</td>
                    <td class="px-6 py-5 text-sm text-right font-black text-slate-800 dark:text-white font-mono">60.000</td>
                    <td class="px-6 py-5 text-center">
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-[var(--primary)] hover:bg-[var(--primary)]/10 transition-all">
                            <span class="material-symbols-outlined text-[20px]">edit_square</span>
                        </button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="bg-[var(--primary)] text-white">
                    <td class="px-6 py-6 text-sm font-black text-right uppercase tracking-widest" colspan="5">Tổng cộng phiếu (Cấp cứu)</td>
                    <td class="px-6 py-6 text-xl font-black text-right font-mono">294.000</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-2 mb-6">
    <div class="flex items-center gap-3 text-xs font-bold text-slate-500 bg-white dark:bg-slate-800 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm">
        <span class="material-symbols-outlined text-[18px]">analytics</span>
        Đã hiển thị 8 trên 156 mặt hàng yêu cầu
    </div>
    <div class="flex items-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1 shadow-sm">
        <button class="w-10 h-10 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-slate-400 transition-colors">
            <span class="material-symbols-outlined">first_page</span>
        </button>
        <button class="w-10 h-10 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-slate-400 transition-colors">
            <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <div class="flex items-center px-4 gap-2">
            <span class="text-xs font-black text-[var(--primary)]">TRANG 1</span>
            <span class="text-xs font-bold text-slate-300">/</span>
            <span class="text-xs font-bold text-slate-400 uppercase">20</span>
        </div>
        <button class="w-10 h-10 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-slate-400 transition-colors">
            <span class="material-symbols-outlined">chevron_right</span>
        </button>
        <button class="w-10 h-10 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-slate-400 transition-colors">
            <span class="material-symbols-outlined">last_page</span>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-[0_4px_12px_rgba(0,0,0,0.03)] hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Tiến độ duyệt</p>
            <span class="material-symbols-outlined text-[var(--primary)]">domain_verification</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-white">20 <span class="text-slate-300 dark:text-slate-600 text-xl">/ 20</span></p>
        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-4 overflow-hidden">
            <div class="bg-[var(--primary)] h-full w-[100%] rounded-full"></div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-[0_4px_12px_rgba(0,0,0,0.03)] hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Sản phẩm duyệt</p>
            <span class="material-symbols-outlined text-amber-500">inventory_2</span>
        </div>
        <p class="text-3xl font-black text-amber-500">1,245</p>
        <p class="text-[11px] font-bold text-slate-400 mt-2 flex items-center gap-1">
            <span class="text-emerald-500">+12%</span> so với tháng trước
        </p>
    </div>
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-[0_4px_12px_rgba(0,0,0,0.03)] hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Ước tính ngân sách</p>
            <span class="material-symbols-outlined text-indigo-500">payments</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-white font-mono">15.4<span class="text-sm ml-1 text-slate-400">TR</span></p>
        <p class="text-[11px] font-bold text-slate-400 mt-2 uppercase">Cập nhật lúc 10:45 AM</p>
    </div>
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-[0_4px_12px_rgba(0,0,0,0.03)] hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Thời gian còn lại</p>
            <span class="material-symbols-outlined text-rose-500">timer</span>
        </div>
        <p class="text-3xl font-black text-slate-800 dark:text-white">02<span class="text-slate-300 text-xl font-normal mx-1">h</span>15<span class="text-slate-300 text-xl font-normal mx-1">m</span></p>
        <p class="text-[11px] font-bold text-rose-500 mt-2 uppercase">Hạn chót hôm nay</p>
    </div>
</div>
@endsection
