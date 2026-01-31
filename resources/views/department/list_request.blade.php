@extends('layouts.department')

@section('title', 'Danh Sách Phiếu Yêu Cầu')

@section('styles')
    <style type="text/tailwindcss">
        .hide-scrollbar::-webkit-scrollbar { display: none; }
                                    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                                </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto w-full space-y-6">

        <!-- Filter and Search Section -->
        <section class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Tìm kiếm</label>
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input id="searchInput"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-0 rounded-lg transition-all"
                            placeholder="Tìm theo mã phiếu, người tạo..." type="text" />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Trạng thái</label>
                    <select id="statusFilter"
                        class="w-full py-2.5 px-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-0 rounded-lg">
                        <option value="">Tất cả</option>
                        <option value="draft">Nháp</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="approved">Đã duyệt</option>
                        <option value="rejected">Từ chối</option>
                        <option value="completed">Hoàn thành</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Thời gian</label>
                    <select id="dateFilter"
                        class="w-full py-2.5 px-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-primary focus:ring-0 rounded-lg">
                        <option value="">Tất cả</option>
                        <option value="today">Hôm nay</option>
                        <option value="week">Tuần này</option>
                        <option value="month">Tháng này</option>
                        <option value="custom">Tùy chỉnh</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-slate-900 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-500">Tổng phiếu</span>
                    <span class="material-symbols-outlined text-slate-400">description</span>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white">{{ $statistics['total'] ?? 0 }}</div>
            </div>

            <div
                class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-5 shadow-sm border border-amber-200 dark:border-amber-800 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Chờ duyệt</span>
                    <span class="material-symbols-outlined text-amber-500">pending</span>
                </div>
                <div class="text-3xl font-black text-amber-700 dark:text-amber-400">{{ $statistics['pending'] ?? 0 }}</div>
            </div>

            <div
                class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 shadow-sm border border-green-200 dark:border-green-800 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-green-700 dark:text-green-400">Đã duyệt</span>
                    <span class="material-symbols-outlined text-green-500">check_circle</span>
                </div>
                <div class="text-3xl font-black text-green-700 dark:text-green-400">{{ $statistics['approved'] ?? 0 }}</div>
            </div>

            <div
                class="bg-red-50 dark:bg-red-900/20 rounded-xl p-5 shadow-sm border border-red-200 dark:border-red-800 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-red-700 dark:text-red-400">Từ chối</span>
                    <span class="material-symbols-outlined text-red-500">cancel</span>
                </div>
                <div class="text-3xl font-black text-red-700 dark:text-red-400">{{ $statistics['rejected'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Request List Table -->
        <section
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-sm w-32">Mã phiếu</th>
                            <th class="px-6 py-4 font-semibold text-sm">Ngày tạo</th>
                            <th class="px-6 py-4 font-semibold text-sm">Người tạo</th>
                            <th class="px-6 py-4 font-semibold text-sm text-center">Số mặt hàng</th>
                            <th class="px-6 py-4 font-semibold text-sm text-right">Tổng tiền</th>
                            <th class="px-6 py-4 font-semibold text-sm text-center">Trạng thái</th>
                            <th class="px-6 py-4 font-semibold text-sm text-center w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($requests ?? [] as $request)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div
                                        class="font-bold {{ $request->status === 'draft' ? 'text-slate-400' : 'text-primary' }}">
                                        {{ $request->request_code }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">{{ $request->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-400">
                                        {{ $request->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                                            {{ strtoupper(substr($request->requester->full_name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-sm">{{ $request->requester->full_name ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-400">
                                                {{ $request->department->department_name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="font-bold">{{ $request->items_count ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @php
                                        $totalAmount = $request->items->sum(function ($item) {
                                            return $item->quantity_requested * $item->product->unit_price;
                                        });
                                    @endphp
                                    <div class="font-bold font-mono">{{ number_format($totalAmount, 0, ',', '.') }}</div>
                                    <div class="text-xs text-slate-400">VNĐ</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'draft' => ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'icon' => 'draft', 'label' => 'Nháp'],
                                            'SUBMITTED' => ['bg' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-700 dark:text-amber-400', 'icon' => 'pending', 'label' => 'Chờ duyệt'],
                                            'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-700 dark:text-amber-400', 'icon' => 'pending', 'label' => 'Chờ duyệt'],
                                            'APPROVED' => ['bg' => 'bg-green-100 dark:bg-green-900/40', 'text' => 'text-green-700 dark:text-green-400', 'icon' => 'check_circle', 'label' => 'Đã duyệt'],
                                            'REJECTED' => ['bg' => 'bg-red-100 dark:bg-red-900/40', 'text' => 'text-red-700 dark:text-red-400', 'icon' => 'cancel', 'label' => 'Từ chối'],
                                            'ISSUED'
                                        ];
                                        $status = $statusConfig[$request->status] ?? $statusConfig['draft'];
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full {{ $status['bg'] }} {{ $status['text'] }} text-xs font-bold">
                                        <span class="material-symbols-outlined text-sm">{{ $status['icon'] }}</span>
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($request->status === 'draft')
                                            <a href="{{ route('department.request.edit', $request->purchase_request_id) }}"
                                                class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Chỉnh sửa">
                                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">edit</span>
                                            </a>
                                            <button onclick="deleteRequest({{ $request->purchase_request_id }})"
                                                class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Xóa">
                                                <span class="material-symbols-outlined text-red-600 dark:text-red-400">delete</span>
                                            </button>
                                        @else
                                            <button onclick="viewRequestDetail({{ $request->purchase_request_id }})"
                                                class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Xem chi tiết">
                                                <span
                                                    class="material-symbols-outlined text-blue-600 dark:text-blue-400">visibility</span>
                                            </button>
                                            @if(in_array($request->status, ['approved', 'completed']))
                                                <button onclick="printRequest({{ $request->purchase_request_id }})"
                                                    class="p-2 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                                                    title="In phiếu">
                                                    <span
                                                        class="material-symbols-outlined text-green-600 dark:text-green-400">print</span>
                                                </button>
                                            @endif
                                            @if($request->status === 'rejected')
                                                <button onclick="recreateRequest({{ $request->purchase_request_id }})"
                                                    class="p-2 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                                    title="Tạo lại">
                                                    <span
                                                        class="material-symbols-outlined text-amber-600 dark:text-amber-400">refresh</span>
                                                </button>
                                            @endif
                                            @if($request->status === 'pending')
                                                <a href="{{ route('department.request.edit', $request->purchase_request_id) }}"
                                                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                                    title="Chỉnh sửa">
                                                    <span
                                                        class="material-symbols-outlined text-slate-600 dark:text-slate-400">edit</span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined text-6xl mb-4">description</span>
                                        <p class="text-lg font-medium">Chưa có phiếu yêu cầu nào</p>
                                        <p class="text-sm mt-2">Hãy tạo phiếu yêu cầu đầu tiên của bạn</p>
                                        <a href="{{ route('department.request.create') }}"
                                            class="mt-4 px-6 py-2.5 bg-primary text-white rounded-lg font-bold hover:bg-sky-700 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                                            <span class="material-symbols-outlined">add</span>
                                            Tạo phiếu mới
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($requests) && $requests->hasPages())
                <div
                    class="bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        Hiển thị <span
                            class="font-bold text-slate-900 dark:text-white">{{ $requests->firstItem() }}-{{ $requests->lastItem() }}</span>
                        trong tổng số <span class="font-bold text-slate-900 dark:text-white">{{ $requests->total() }}</span>
                        phiếu
                    </div>
                    <div class="flex items-center gap-2">
                        {{ $requests->links() }}
                    </div>
                </div>
            @endif
        </section>

        <!-- Request Detail Modal -->
        <div id="requestDetailModal"
            class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-primary to-sky-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-white text-3xl">description</span>
                        <div>
                            <h3 class="text-xl font-bold text-white" id="modalRequestCode">Chi tiết yêu cầu</h3>
                            <p class="text-sky-100 text-sm" id="modalRequestDate"></p>
                        </div>
                    </div>
                    <button onclick="closeRequestModal()" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-white">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 space-y-6">
                    <!-- Request Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                            <div class="text-xs font-bold text-slate-500 uppercase mb-1">Người tạo</div>
                            <div class="font-bold text-slate-900 dark:text-white" id="modalRequester"></div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                            <div class="text-xs font-bold text-slate-500 uppercase mb-1">Khoa phòng</div>
                            <div class="font-bold text-slate-900 dark:text-white" id="modalDepartment"></div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                            <div class="text-xs font-bold text-slate-500 uppercase mb-1">Trạng thái</div>
                            <div id="modalStatus"></div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                            <div class="text-xs font-bold text-slate-500 uppercase mb-1">Số mặt hàng</div>
                            <div class="font-bold text-slate-900 dark:text-white" id="modalItemsCount"></div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 bg-slate-200 dark:bg-slate-700">
                            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined">inventory_2</span>
                                Danh sách sản phẩm
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-100 dark:bg-slate-700/50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300">
                                            STT</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Tên sản phẩm</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Mã sản phẩm</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Đơn vị</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Số lượng</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Đơn giá</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-slate-600 dark:text-slate-300">
                                            Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody id="modalProductsTable" class="divide-y divide-slate-200 dark:divide-slate-700">
                                    <!-- Products will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Amount -->
                    <div
                        class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border-2 border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-slate-700 dark:text-slate-300">Tổng tiền:</span>
                            <div class="text-right">
                                <div class="text-2xl font-black text-green-700 dark:text-green-400 font-mono"
                                    id="modalTotalAmount"></div>
                                <div class="text-xs text-green-600 dark:text-green-500">VNĐ</div>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div id="modalNoteSection"
                        class="hidden bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
                        <div
                            class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">note</span>
                            Ghi chú
                        </div>
                        <div class="text-sm text-slate-700 dark:text-slate-300" id="modalNote"></div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="bg-slate-50 dark:bg-slate-800 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                    <button onclick="closeRequestModal()"
                        class="px-6 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                        Đóng
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        // Filter functionality
        document.getElementById('searchInput')?.addEventListener('input', function (e) {
            // Implement search functionality
            console.log('Search:', e.target.value);
        });

        document.getElementById('statusFilter')?.addEventListener('change', function (e) {
            // Implement status filter
            console.log('Status filter:', e.target.value);
        });

        document.getElementById('dateFilter')?.addEventListener('change', function (e) {
            // Implement date filter
            console.log('Date filter:', e.target.value);
        });

        // Delete request
        function deleteRequest(id) {
            if (confirm('Bạn có chắc chắn muốn xóa phiếu yêu cầu này?')) {
                // Implement delete functionality
                console.log('Delete request:', id);
            }
        }

        // Print request
        function printRequest(id) {
            window.open(`/department/request/${id}/print`, '_blank');
        }

        // Recreate request
        function recreateRequest(id) {
            if (confirm('Bạn có muốn tạo lại phiếu yêu cầu này?')) {
                window.location.href = `/department/request/${id}/recreate`;
            }
        }

        // View request detail in modal
        function viewRequestDetail(id) {
            const modal = document.getElementById('requestDetailModal');
            modal.classList.remove('hidden');

            // Fetch request details
            fetch(`/department/request/${id}/detail`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const data = result.data;

                        // Update modal header
                        document.getElementById('modalRequestCode').textContent = data.request_code;
                        document.getElementById('modalRequestDate').textContent = data.request_date;

                        // Update request info
                        document.getElementById('modalRequester').textContent = data.requester_name;
                        document.getElementById('modalDepartment').textContent = data.department_name;
                        document.getElementById('modalItemsCount').textContent = data.items_count;

                        // Update status badge
                        const statusConfig = {
                            'draft': { bg: 'bg-slate-100 dark:bg-slate-800', text: 'text-slate-600 dark:text-slate-400', icon: 'draft' },
                            'SUBMITTED': { bg: 'bg-amber-100 dark:bg-amber-900/40', text: 'text-amber-700 dark:text-amber-400', icon: 'pending' },
                            'pending': { bg: 'bg-amber-100 dark:bg-amber-900/40', text: 'text-amber-700 dark:text-amber-400', icon: 'pending' },
                            'APPROVED': { bg: 'bg-green-100 dark:bg-green-900/40', text: 'text-green-700 dark:text-green-400', icon: 'check_circle' },
                            'REJECTED': { bg: 'bg-red-100 dark:bg-red-900/40', text: 'text-red-700 dark:text-red-400', icon: 'cancel' },
                            'ISSUED': { bg: 'bg-blue-100 dark:bg-blue-900/40', text: 'text-blue-700 dark:text-blue-400', icon: 'local_shipping' }
                        };
                        const status = statusConfig[data.status] || statusConfig['draft'];
                        document.getElementById('modalStatus').innerHTML = `
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full ${status.bg} ${status.text} text-xs font-bold">
                                                <span class="material-symbols-outlined text-sm">${status.icon}</span>
                                                ${data.status_label}
                                            </span>
                                        `;

                        // Update products table
                        const tbody = document.getElementById('modalProductsTable');
                        tbody.innerHTML = '';
                        data.items.forEach((item, index) => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-slate-100 dark:hover:bg-slate-700/30';
                            row.innerHTML = `
                                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">${index + 1}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">${item.product_name}</td>
                                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">${item.product_code}</td>
                                                <td class="px-4 py-3 text-sm text-center text-slate-600 dark:text-slate-400">${item.unit}</td>
                                                <td class="px-4 py-3 text-sm text-right font-bold text-slate-900 dark:text-white">${item.quantity_requested}</td>
                                                <td class="px-4 py-3 text-sm text-right font-mono text-slate-600 dark:text-slate-400">${formatNumber(item.unit_price)}</td>
                                                <td class="px-4 py-3 text-sm text-right font-mono font-bold text-slate-900 dark:text-white">${formatNumber(item.total_price)}</td>
                                            `;
                            tbody.appendChild(row);
                        });

                        // Update total amount
                        document.getElementById('modalTotalAmount').textContent = formatNumber(data.total_amount);

                        // Update note
                        const noteSection = document.getElementById('modalNoteSection');
                        if (data.note && data.note.trim() !== '') {
                            noteSection.classList.remove('hidden');
                            document.getElementById('modalNote').textContent = data.note;
                        } else {
                            noteSection.classList.add('hidden');
                        }
                    } else {
                        alert(result.message || 'Không thể tải thông tin yêu cầu');
                        closeRequestModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi tải thông tin yêu cầu');
                    closeRequestModal();
                });
        }

        // Close modal
        function closeRequestModal() {
            document.getElementById('requestDetailModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('requestDetailModal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeRequestModal();
            }
        });

        // Format number with thousand separators
        function formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeRequestModal();
            }
        });
    </script>
@endsection