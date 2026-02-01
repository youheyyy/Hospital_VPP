@extends('layouts.admin')

@section('title', 'Danh sách Đơn hàng Mua sắm (PO)')

@section('page-title', 'Quản lý Đơn hàng (PO)')

@section('content')
    <div x-data="{ showModal: false, selectedPO: null }" 
         @open-po-modal.window="showModal = true; selectedPO = $event.detail.id; loadPODetails($event.detail.id)"
         class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-6 -mx-8 -mt-8">
        <div
            class="px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-600 p-2.5 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-white text-2xl">local_shipping</span>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-800 dark:text-white">Danh sách Đơn hàng</h1>
                    <p class="text-xs font-medium text-slate-500 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">inventory</span>
                        Quản lý Purchase Orders (PO)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" placeholder="Tìm kiếm PO..."
                        class="pl-10 pr-4 py-2 text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-slate-800 dark:border-slate-700 dark:text-white w-64">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th
                            class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center w-16">
                            STT</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Mã Đơn hàng
                        </th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Khoa / Phòng
                        </th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Nhà cung cấp
                        </th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">
                            Ngày tạo</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">
                            Tổng tiền</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">
                            Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">Tác
                            vụ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $index => $order)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm text-center text-slate-400 font-medium">
                                {{ $orders->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $order->order_code }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                    {{ $order->department->department_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-600 dark:text-slate-300">
                                    {{ $order->supplier->supplier_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-bold text-slate-500">
                                    {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-black font-mono text-slate-800 dark:text-white">
                                    {{ number_format($order->total_amount, 0, ',', '.') }} VNĐ</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($order->status == 'APPROVED')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Đã duyệt
                                    </span>
                                @elseif($order->status == 'ISSUED')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Đã xuất
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ $order->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="$dispatch('open-po-modal', { id: {{ $order->purchase_order_id }} })" 
                                    class="text-slate-400 hover:text-indigo-600 transition-colors p-2 hover:bg-indigo-50 rounded-lg inline-flex" 
                                    title="Xem chi tiết">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">shopping_cart_off</span>
                                    <p>Không có đơn hàng nào.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $orders->links() }}
            </div>
        @endif

        <!-- PO Detail Modal -->
        <div x-show="showModal" 
             x-cloak
             @keydown.escape.window="showModal = false"
             class="fixed inset-0 z-50 overflow-hidden"
             style="display: none;">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                 @click="showModal = false"></div>
            
            <!-- Modal Panel -->
            <div class="absolute inset-y-0 right-0 max-w-4xl w-full bg-white dark:bg-slate-900 shadow-2xl transform transition-transform"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <div class="h-full flex flex-col" x-data="{ poData: null, loading: true }">
                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                        <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white">Chi tiết Đơn hàng</h2>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <span class="material-symbols-outlined text-2xl">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="flex-1 overflow-y-auto p-8" id="po-detail-content">
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                                <p class="text-slate-500">Đang tải dữ liệu...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    function loadPODetails(poId) {
        const contentDiv = document.getElementById('po-detail-content');
        
        fetch(`/admin/orders/${poId}`)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML and extract the content section
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.querySelector('.max-w-5xl');
                
                if (content) {
                    // Remove the back button
                    const backButton = content.querySelector('a[href*="orders.index"]');
                    if (backButton) {
                        backButton.parentElement.remove();
                    }
                    
                    contentDiv.innerHTML = content.innerHTML;
                } else {
                    contentDiv.innerHTML = '<p class="text-center text-red-500">Không thể tải dữ liệu</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contentDiv.innerHTML = '<p class="text-center text-red-500">Đã xảy ra lỗi khi tải dữ liệu</p>';
            });
    }
</script>
@endpush
@endsection