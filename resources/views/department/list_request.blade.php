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
                                    <div class="text-xs text-slate-400">{{ $request->created_at->format('h:i A') }}</div>
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
                                    <span class="font-bold">{{ $request->items_count ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @php
                                        $totalAmount = $request->items->sum(function ($item) {
                                            return $item->quantity_requested * $item->product->unit_price;
                                        });
                                    @endphp
                                    <div class="font-bold font-mono">{{ number_format($totalAmount) }}</div>
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
                                            <a href="{{ route('department.request.show', $request->purchase_request_id) }}"
                                                class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Xem chi tiết">
                                                <span
                                                    class="material-symbols-outlined text-blue-600 dark:text-blue-400">visibility</span>
                                            </a>
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
    </script>
@endsection