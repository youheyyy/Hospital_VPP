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
                <form action="{{ route('department.dashboard') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative group">
                        <select name="month" onchange="this.form.submit()" 
                            class="glass-card appearance-none pl-10 pr-4 py-2 rounded-lg text-xs font-bold hover:bg-white transition-all cursor-pointer outline-none">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                    {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[16px] pointer-events-none">calendar_today</span>
                    </div>
                    <div class="relative group">
                        <select name="year" onchange="this.form.submit()" 
                            class="glass-card appearance-none pl-4 pr-10 py-2 rounded-lg text-xs font-bold hover:bg-white transition-all cursor-pointer outline-none">
                            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
                <button
                    class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold shadow-lg">
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
                    <div
                        class="p-2 rounded-xl bg-gradient-to-tr from-orange-400 to-red-500 shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-white !text-xl">pending_actions</span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang chờ duyệt</span>
                        <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ str_pad($pendingCount, 2, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>
                <div class="h-10 w-full">
                    <svg class="w-full h-full" viewBox="0 0 100 20">
                        <path class="sparkline stroke-orange-500"
                            d="M0,15 L10,12 L20,18 L30,14 L40,16 L50,10 L60,12 L70,8 L80,10 L90,5 L100,7" fill="none">
                        </path>
                    </svg>
                </div>
                <p class="text-[10px] font-semibold text-orange-600 dark:text-orange-400 mt-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[12px]">info</span>
                    Phiếu chờ cấp quản lý duyệt
                </p>
            </div>

            <!-- Card 2: Đã duyệt -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
                <div class="flex justify-between items-start mb-6">
                    <div
                        class="p-2 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-500 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-white !text-xl">check_circle</span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đã được duyệt</span>
                        <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ str_pad($approvedCount, 2, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>
                <div class="h-10 w-full">
                    <svg class="w-full h-full" viewBox="0 0 100 20">
                        <path class="sparkline stroke-emerald-500"
                            d="M0,18 L10,15 L20,10 L30,12 L40,8 L50,6 L60,10 L70,4 L80,6 L90,2 L100,5" fill="none"></path>
                    </svg>
                </div>
                <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 mt-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[12px]">verified</span>
                    Phiếu đã sẵn sàng nhận hàng
                </p>
            </div>

            <!-- Card 3: Tổng yêu cầu -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
                <div class="flex justify-between items-start mb-6">
                    <div
                        class="p-2 rounded-xl bg-gradient-to-tr from-blue-400 to-indigo-500 shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-white !text-xl">description</span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng số phiếu</span>
                        <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ str_pad($totalCount, 2, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>
                <div class="h-10 w-full">
                    <svg class="w-full h-full" viewBox="0 0 100 20">
                        <path class="sparkline stroke-blue-500" d="M0,10 L20,10 L40,10 L60,8 L80,5 L100,2" fill="none">
                        </path>
                    </svg>
                </div>
                <p class="text-[10px] font-semibold text-slate-500 mt-3 font-bold uppercase tracking-tighter">Số liệu năm
                    {{ date('Y') }}</p>
            </div>

            <!-- Card 4: Nhu cầu vật tư -->
            <div class="glass-card p-6 rounded-3xl relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nhu cầu vật tư</span>
                    </div>
                    <span class="material-symbols-outlined text-purple-500 !text-lg">auto_awesome</span>
                </div>
                <div class="space-y-4">
                    @forelse($topDemands as $demand)
                    <div>
                        <div class="flex justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                            <span class="truncate pr-2">{{ $demand->product->product_name }}</span>
                            <span class="text-purple-600">{{ $demand->percentage }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-500 rounded-full" style="width: {{ $demand->percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-slate-400 text-[10px] font-medium italic">Không có dữ liệu trong tháng</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Requests Table -->
        <div class="glass-card rounded-2xl overflow-hidden border-none shadow-xl shadow-slate-200/40">
            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Phiếu yêu cầu gần đây</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Theo dõi tiến độ xử lý văn phòng phẩm theo thời
                        gian thực</p>
                </div>
                <div class="flex items-center gap-4">
                    <form action="{{ route('department.dashboard') }}" method="GET" id="statusFilterForm">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 !text-sm">filter_list</span>
                            <select name="status" onchange="this.form.submit()" 
                                class="bg-slate-50 dark:bg-slate-800 border-none rounded-lg pl-9 pr-4 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 outline-none cursor-pointer">
                                <option value="">Tất cả trạng thái</option>
                                <option value="SUBMITTED" {{ $status == 'SUBMITTED' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="APPROVED" {{ $status == 'APPROVED' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="REJECTED" {{ $status == 'REJECTED' ? 'selected' : '' }}>Từ chối</option>
                                <option value="ISSUED" {{ $status == 'ISSUED' ? 'selected' : '' }}>Đã phát</option>
                            </select>
                        </div>
                    </form>
                    <a href="{{ route('department.list_request') }}"
                        class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 text-xs font-bold px-4 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                        Xem tất cả
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-800/30 text-slate-400 text-[10px] font-black uppercase tracking-[0.12em]">
                            <th class="px-5 py-3">Mã phiếu</th>
                            <th class="px-5 py-3">Thời gian</th>
                            <th class="px-5 py-3">Trường hợp</th>
                            <th class="px-5 py-3">Người tạo</th>
                            <th class="px-5 py-3 text-center">Trạng thái</th>
                            <th class="px-5 py-3 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($requests as $request)
                            <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/5 transition-all group">
                                <td class="px-5 py-3">
                                    <span
                                        class="font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-lg text-xs">{{ $request->request_code }}</span>
                                </td>
                                <td class="px-5 py-3 text-xs font-semibold text-slate-500">
                                    {{ $request->created_at->diffForHumans() }}
                                </td>
                                <td class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-200">
                                    {{ $request->request_items_count ?? $request->items->count() }} mặt hàng
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-[9px] flex items-center justify-center font-bold">
                                            {{ strtoupper(substr($request->requester->full_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span
                                            class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $request->requester->full_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @php
                                        $statusMap = [
                                            'SUBMITTED' => ['label' => 'CHỜ DUYỆT', 'color' => 'orange', 'icon' => 'pending'],
                                            'APPROVED' => ['label' => 'ĐÃ DUYỆT', 'color' => 'emerald', 'icon' => 'check_circle'],
                                            'REJECTED' => ['label' => 'TỪ CHỐI', 'color' => 'red', 'icon' => 'cancel'],
                                            'ISSUED' => ['label' => 'ĐÃ PHÁT', 'color' => 'blue', 'icon' => 'local_shipping'],
                                        ];
                                        $s = $statusMap[$request->status] ?? ['label' => $request->status, 'color' => 'slate', 'icon' => 'info'];
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black bg-{{ $s['color'] }}-500/10 text-{{ $s['color'] }}-600 dark:text-{{ $s['color'] }}-400 border border-{{ $s['color'] }}-500/20 status-badge-glow ring-1 ring-{{ $s['color'] }}-500/5">
                                        <span
                                            class="w-1 h-1 rounded-full bg-{{ $s['color'] }}-500 mr-1.5 shadow-[0_0_4px_rgba(var(--tw-color-{{ $s['color'] }}-500),1)]"></span>
                                        {{ $s['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button onclick="viewRequestDetail({{ $request->purchase_request_id }})"
                                        class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors text-blue-600 dark:text-blue-400"
                                        title="Xem chi tiết">
                                        <span class="material-symbols-outlined text-xl">visibility</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm italic">Chưa có yêu cầu nào
                                    gần đây</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            @if($requests->hasPages())
                <div
                    class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/30">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Trang {{ $requests->currentPage() }} / {{ $requests->lastPage() }}
                    </div>
                    <div class="flex gap-2">
                        @if ($requests->onFirstPage())
                            <button
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-300 cursor-not-allowed">
                                <span class="material-symbols-outlined !text-lg">chevron_left</span>
                            </button>
                        @else
                            <a class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                href="{{ $requests->previousPageUrl() }}">
                                <span class="material-symbols-outlined !text-lg">chevron_left</span>
                            </a>
                        @endif

                        @if ($requests->hasMorePages())
                            <a class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                href="{{ $requests->nextPageUrl() }}">
                                <span class="material-symbols-outlined !text-lg">chevron_right</span>
                            </a>
                        @else
                            <button
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-300 cursor-not-allowed">
                                <span class="material-symbols-outlined !text-lg">chevron_right</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Request Detail Modal -->
    <div class="hidden fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center p-4"
        id="requestDetailModal">
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-white/20">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-md">
                        <span class="material-symbols-outlined text-white text-3xl">description</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight" id="modalRequestCode">Chi tiết yêu cầu</h3>
                        <p class="text-blue-100 text-xs font-bold uppercase tracking-widest" id="modalRequestDate"></p>
                    </div>
                </div>
                <button class="w-10 h-10 flex items-center justify-center hover:bg-white/20 rounded-xl transition-colors text-white"
                    onclick="closeRequestModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-8 space-y-8">
                <!-- Request Info Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 text-center">Người
                            tạo</div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm text-center" id="modalRequester"></div>
                    </div>
                    <div
                        class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 text-center">Khoa
                            phòng</div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm text-center" id="modalDepartment"></div>
                    </div>
                    <div
                        class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 text-center">Trạng
                            thái</div>
                        <div class="flex justify-center" id="modalStatusBadge"></div>
                    </div>
                    <div
                        class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 text-center">Số
                            mặt hàng</div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm text-center" id="modalItemsCount"></div>
                    </div>
                </div>

                <!-- Products Table -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-4 py-3 text-center">STT</th>
                                <th class="px-4 py-3">Tên sản phẩm</th>
                                <th class="px-4 py-3 text-center w-24">Đơn vị</th>
                                <th class="px-4 py-3 text-center w-24">Số lượng</th>
                                <th class="px-4 py-3 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800" id="modalProductsTable">
                            <!-- Rows injected here -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div
                    class="flex flex-col md:flex-row items-center justify-between gap-6 bg-slate-900 rounded-3xl p-6 text-white shadow-xl shadow-blue-900/10">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1">Tổng cộng (VNĐ)</p>
                        <p class="text-3xl font-black text-white font-mono tracking-tighter" id="modalTotalAmount">0</p>
                    </div>
                    <button
                        class="w-full md:w-auto px-8 py-3 bg-white/10 hover:bg-white/20 rounded-2xl text-sm font-bold transition-all border border-white/20 backdrop-blur-sm"
                        onclick="closeRequestModal()">
                        ĐÓNG CỬA SỔ
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function viewRequestDetail(id) {
            const modal = document.getElementById('requestDetailModal');
            modal.classList.remove('hidden');

            fetch(`/department/request/${id}/detail`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const data = result.data;
                        document.getElementById('modalRequestCode').textContent = data.request_code;
                        document.getElementById('modalRequestDate').textContent = data.request_date;
                        document.getElementById('modalRequester').textContent = data.requester_name;
                        document.getElementById('modalDepartment').textContent = data.department_name;
                        document.getElementById('modalItemsCount').textContent = data.items_count + ' SP';
                        document.getElementById('modalTotalAmount').textContent = formatNumber(data.total_amount);

                        const statusConfig = {
                            'SUBMITTED': {
                                label: 'CHỜ DUYỆT',
                                color: 'orange'
                            },
                            'APPROVED': {
                                label: 'ĐÃ DUYỆT',
                                color: 'emerald'
                            },
                            'REJECTED': {
                                label: 'TỪ CHỐI',
                                color: 'red'
                            },
                            'ISSUED': {
                                label: 'ĐÃ PHÁT',
                                color: 'blue'
                            }
                        };
                        const sc = statusConfig[data.status] || {
                            label: data.status,
                            color: 'slate'
                        };
                        document.getElementById('modalStatusBadge').innerHTML = `
                        <span class="px-3 py-1 rounded-full text-[9px] font-black bg-${sc.color}-500/10 text-${sc.color}-600 border border-${sc.color}-500/20">
                            ${sc.label}
                        </span>
                    `;

                        const tbody = document.getElementById('modalProductsTable');
                        tbody.innerHTML = '';
                        data.items.forEach((item, index) => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors';
                            tr.innerHTML = `
                            <td class="px-4 py-4 text-center font-mono text-[10px] text-slate-400 font-bold">${index + 1}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">${item.product_name}</div>
                                <div class="text-[9px] text-slate-400 font-mono uppercase">${item.product_code}</div>
                            </td>
                            <td class="px-4 py-4 text-center text-[10px] font-bold text-slate-500">${item.unit}</td>
                            <td class="px-4 py-4 text-center text-xs font-black text-slate-800 dark:text-white">${item.quantity_requested}</td>
                            <td class="px-4 py-4 text-right font-mono text-xs font-black text-blue-600">${formatNumber(item.total_price)}</td>
                        `;
                            tbody.appendChild(tr);
                        });
                    }
                });
        }

        function closeRequestModal() {
            document.getElementById('requestDetailModal').classList.add('hidden');
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }

        // Close on backdrop click
        document.getElementById('requestDetailModal').addEventListener('click', function(e) {
            if (e.target === this) closeRequestModal();
        });
    </script>
@endsection