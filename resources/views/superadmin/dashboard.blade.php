@extends('layouts.superadmin')

@section('title', 'Dashboard')

@section('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endsection

@section('content')
    <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Chào mừng trở lại, SuperAdmin!</h1>
            <p class="text-gray-500 text-sm">Đây là tổng quan hệ thống của bạn hôm nay.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Departments -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">corporate_fare</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tổng số khoa</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($totalDepartments) }}</h3>
                </div>
            </div>

            <!-- Total Users -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">group</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Người dùng</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($totalUsers) }}</h3>
                </div>
            </div>

            <!-- Monthly Orders -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">shopping_cart</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Đơn hàng tháng</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($monthlyOrdersCount) }}</h3>
                </div>
            </div>
        </div>

        <!-- Trend Chart (Full width) with Toggle -->
        <div class="mb-8 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm" x-data="activityChart">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2.5">
                        <span class="size-2.5 rounded-full bg-indigo-600 shadow-[0_0_10px_rgba(79,70,229,0.5)]"></span>
                        XU HƯỚNG ĐƠN HÀNG (6 THÁNG QUA)
                    </h2>
                    <p class="text-xs text-gray-400 font-medium mt-1">Phân tích tần suất và lưu lượng mua sắm hệ thống</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Total Value Badge -->
                    <div class="bg-indigo-600 text-white px-4 py-2 rounded-xl shadow-lg">
                        <span class="text-sm font-bold" x-text="getTotalValue()"></span>
                    </div>

                    <!-- Toggle Control -->
                    <div class="bg-gray-100/80 p-1.5 rounded-2xl flex items-center gap-1 shadow-inner">
                        <button @click="chartType = 'qty'"
                            :class="chartType === 'qty' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:bg-white/50'"
                            class="px-5 py-2 rounded-xl text-xs font-bold transition-all duration-300">
                            Số lượng
                        </button>
                        <button @click="chartType = 'val'"
                            :class="chartType === 'val' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:bg-white/50'"
                            class="px-5 py-2 rounded-xl text-xs font-bold transition-all duration-300">
                            Giá trị
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative pt-12">
                <!-- Grid Lines -->
                <div class="absolute inset-x-0 top-12 bottom-12 flex flex-col justify-between pointer-events-none">
                    <div class="border-t border-gray-50 w-full h-0"></div>
                    <div class="border-t border-gray-50 w-full h-0"></div>
                    <div class="border-t border-gray-50 w-full h-0"></div>
                    <div class="border-t border-gray-50 w-full h-0"></div>
                </div>

                <div class="h-80 flex items-end justify-between gap-8 px-6 relative z-10">
                    <template x-for="trend in trendsData">
                        <div class="flex-1 flex flex-col items-center gap-5 group">
                            <div class="relative w-full max-w-[80px]">
                                <!-- Value Label -->
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-4">
                                    <span
                                        class="bg-indigo-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl shadow-lg shadow-indigo-200/50 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-300 -translate-y-2 group-hover:translate-y-0"
                                        :class="nowFormat(trend) === '{{ now()->format('m/Y') }}' ? 'opacity-100 translate-y-0 bg-indigo-600' : ''">
                                        <span x-text="formatValue(trend)"></span>
                                        <span x-text="chartType === 'qty' ? ' đơn' : ' đ'"></span>
                                    </span>
                                </div>

                                <!-- Bar Container -->
                                <div class="w-full bg-gray-50 rounded-t-2xl transition-all duration-500 flex items-end overflow-hidden"
                                    :style="`height: ${Math.max(15, getHeight(trend) * 2.5)}px`"
                                    @mouseenter="$el.closest('.group').classList.add('active')">

                                    <!-- Actual Bar -->
                                    <div class="w-full rounded-t-2xl transition-all duration-500"
                                        :class="nowFormat(trend) === '{{ now()->format('m/Y') }}' ? 'bg-indigo-600 shadow-lg shadow-indigo-300/50' : 'bg-indigo-300/60 group-hover:bg-indigo-400/80'"
                                        :style="`height: 100%`" x-transition:enter="transition ease-out duration-1000"
                                        x-transition:enter-start="h-0" x-transition:enter-end="h-full">
                                    </div>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-widest transition-colors"
                                :class="nowFormat(trend) === '{{ now()->format('m/Y') }}' ? 'text-slate-800' : 'text-gray-400 group-hover:text-indigo-500'"
                                x-text="'THÁNG ' + trend.month.split('/')[0]"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Row 3: Login Sessions & Activity Log -->
        {{--
        <div class="grid grid-cols-1 gap-8 mb-8">
            <!-- Activity Log (Row 4) -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm" x-data="activityStats">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="size-2 rounded-full bg-purple-600"></span>
                        NHẬT KÝ HOẠT ĐỘNG
                    </h2>
                    <button @click="openModal()"
                        class="text-xs font-bold text-purple-600 hover:text-purple-800 hover:underline flex items-center gap-1">
                        Xem tất cả
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                    </button>
                </div>

                <div class="overflow-x-auto min-h-[250px]">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">NGƯỜI
                                    DÙNG
                                </th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">HÀNH
                                    ĐỘNG
                                </th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">THỜI
                                    GIAN
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="log in pagedDashboard" :key="log.id">
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="size-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px]"
                                                x-text="getInitials(log.user.name)"></div>
                                            <span class="text-sm font-bold text-slate-900" x-text="log.user.name"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span :class="getActionClass(log.action)"
                                                class="size-1.5 rounded-full shrink-0"></span>
                                            <p class="text-xs text-gray-600 font-medium line-clamp-1"
                                                x-text="log.description">
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[10px] text-gray-400 font-bold uppercase"
                                            x-text="formatTime(log.created_at)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Simple Pagination for Dashboard (3 items) -->
                    <div class="flex justify-between items-center mt-4 px-2" x-show="totalDashboardPages > 1">
                        <span class="text-[10px] text-gray-400 font-bold">TRANG <span x-text="dashboardPage"></span>/<span
                                x-text="totalDashboardPages"></span></span>
                        <div class="flex gap-2">
                            <button @click="dashboardPage--" :disabled="dashboardPage <= 1"
                                class="p-1 rounded hover:bg-gray-100 disabled:opacity-30">
                                <span class="material-symbols-outlined text-sm">chevron_left</span>
                            </button>
                            <button @click="dashboardPage++" :disabled="dashboardPage >= totalDashboardPages"
                                class="p-1 rounded hover:bg-gray-100 disabled:opacity-30">
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- View All Modal -->
                <div x-show="modalOpen" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                    @click.self="modalOpen = false">
                    <div
                        class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">NHẬT KÝ HOẠT ĐỘNG HỆ THỐNG</h3>
                                <p class="text-xs text-gray-500 font-medium">Toàn bộ lịch sử thao tác của các quản trị viên
                                </p>
                            </div>
                            <button @click="modalOpen = false"
                                class="size-10 rounded-full hover:bg-gray-200 flex items-center justify-center transition-colors text-gray-400">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="flex-1 overflow-auto p-0">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 border-b sticky top-0 z-10">
                                    <tr>
                                        <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase bg-gray-50">NGƯỜI
                                            DÙNG</th>
                                        <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase bg-gray-50">HÀNH
                                            ĐỘNG
                                        </th>
                                        <th
                                            class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase bg-gray-50 text-right">
                                            THỜI GIAN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 uppercase">
                                    <template x-for="log in pagedModal" :key="log.id">
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="px-8 py-5">
                                                <div class="flex items-center gap-4">
                                                    <div class="size-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs"
                                                        x-text="getInitials(log.user.name)"></div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-900" x-text="log.user.name">
                                                        </p>
                                                        <p class="text-[9px] text-gray-400 font-bold"
                                                            x-text="log.user.department?.name || 'HỆ THỐNG'"></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <div class="flex items-center gap-2">
                                                    <span :class="getActionClass(log.action)"
                                                        class="size-2 rounded-full"></span>
                                                    <p class="text-[11px] text-slate-700 font-bold leading-relaxed"
                                                        x-text="log.description"></p>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5 text-right">
                                                <span class="text-[10px] text-slate-400 font-bold"
                                                    x-text="formatFullTime(log.created_at)"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 border-t bg-gray-50 flex justify-between items-center">
                            <p class="text-xs text-gray-500 font-bold">HIỂN THỊ <span
                                    x-text="Math.min(modalPage * 10, allLogs.length)"></span> / <span
                                    x-text="allLogs.length"></span> HOẠT ĐỘNG</p>
                            <div class="flex items-center gap-4">
                                <button @click="modalPage--" :disabled="modalPage <= 1"
                                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-white transition-all disabled:opacity-30">
                                    <span class="material-symbols-outlined text-sm">west</span> TRANG TRƯỚC
                                </button>
                                <span class="text-sm font-bold text-slate-900" x-text="modalPage"></span>
                                <button @click="modalPage++" :disabled="modalPage >= totalModalPages"
                                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-white transition-all disabled:opacity-30">
                                    TRANG SAU <span class="material-symbols-outlined text-sm">east</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        --}}
@endsection

    @section('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('activityStats', () => ({
                    allLogs: @js($recentActivities),
                    dashboardPage: 1,
                    modalPage: 1,
                    modalOpen: false,

                    get pagedDashboard() {
                        const start = (this.dashboardPage - 1) * 3;
                        return this.allLogs.slice(start, start + 3);
                    },
                    get totalDashboardPages() {
                        return Math.ceil(this.allLogs.length / 3);
                    },

                    get pagedModal() {
                        const start = (this.modalPage - 1) * 10;
                        return this.allLogs.slice(start, start + 10);
                    },
                    get totalModalPages() {
                        return Math.ceil(this.allLogs.length / 10);
                    },

                    openModal() {
                        this.modalOpen = true;
                        this.modalPage = 1;
                    },

                    getInitials(name) {
                        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    },

                    getActionClass(action) {
                        if (action.includes('Xóa')) return 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]';
                        if (action.includes('Thêm') || action.includes('Import')) return 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]';
                        return 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.4)]';
                    },

                    formatTime(timestamp) {
                        const date = new Date(timestamp);
                        const diff = (new Date() - date) / 1000;
                        if (diff < 60) return 'Vừa xong';
                        if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
                        if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
                        return date.toLocaleDateString('vi-VN');
                    },

                    formatFullTime(timestamp) {
                        return new Date(timestamp).toLocaleString('vi-VN', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                    }
                }));

                Alpine.data('activityChart', () => ({
                    trendsData: @js($monthlyTrends),
                    chartType: 'qty',

                    getTotalValue() {
                        if (!this.trendsData || this.trendsData.length === 0) return '0';
                        const total = this.trendsData.reduce((sum, t) => {
                            return sum + (this.chartType === 'qty' ? t.total_qty : t.total_value);
                        }, 0);
                        const formatted = new Intl.NumberFormat('vi-VN').format(total);
                        return this.chartType === 'qty' ? `${formatted} đơn` : `${formatted} đ`;
                    },

                    getHeight(trend) {
                        if (!this.trendsData || this.trendsData.length === 0) return 0;
                        const values = this.trendsData.map(t => this.chartType === 'qty' ? t.total_qty : t.total_value);
                        const max = Math.max(...values, 1);
                        const val = this.chartType === 'qty' ? trend.total_qty : trend.total_value;
                        return (val / max) * 100;
                    },

                    formatValue(trend) {
                        const val = this.chartType === 'qty' ? trend.total_qty : trend.total_value;
                        return new Intl.NumberFormat('vi-VN').format(val);
                    },

                    nowFormat(trend) {
                        return trend.month;
                    }
                }));
            });
        </script>
    @endsection