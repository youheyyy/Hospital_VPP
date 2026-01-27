@extends('layouts.admin')

@section('title', 'Chi tiết Đơn hàng ' . $order->order_code)

@section('page-title', 'Chi tiết Đơn hàng')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.orders.index') }}"
                class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-medium">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Quay lại danh sách
            </a>
            <div class="flex items-center gap-3">
                @if($order->status == 'APPROVED')
                    <form action="{{ route('admin.orders.update_status', $order->purchase_order_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="ISSUED">
                        <button type="submit" onclick="return confirm('Xác nhận đơn hàng đã xuất/giao?')"
                            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all">
                            <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                            <span class="text-sm">Xác nhận Đã giao (ISSUED)</span>
                        </button>
                    </form>
                @endif
                <button onclick="window.print()"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-lg shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[20px]">print</span>
                    <span class="text-sm">In Đơn hàng</span>
                </button>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ $order->order_code }}</h1>
                            @if($order->status == 'APPROVED')
                                <span
                                    class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full uppercase border border-emerald-200">Đã
                                    duyệt</span>
                            @elseif($order->status == 'ISSUED')
                                <span
                                    class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase border border-blue-200">Đã
                                    xuất</span>
                            @else
                                <span
                                    class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full uppercase border border-slate-200">{{ $order->status }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 font-medium">Ngày tạo:
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Tổng giá trị</p>
                        <p class="text-2xl font-black font-mono text-indigo-600 dark:text-indigo-400">
                            {{ number_format($order->total_amount) }} ₫</p>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-2 gap-8 px-8 py-8 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Thông tin Nhà cung cấp</h3>
                    <div class="flex items-start gap-4">
                        <div class="bg-indigo-50 p-3 rounded-lg text-indigo-600">
                            <span class="material-symbols-outlined text-2xl">storefront</span>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-800 dark:text-white mb-1">
                                {{ $order->supplier->supplier_name }}</p>
                            <p class="text-sm text-slate-500 mb-1 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">location_on</span>
                                {{ $order->supplier->address ?? 'Chưa cập nhật địa chỉ' }}
                            </p>
                            <p class="text-sm text-slate-500 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">phone</span>
                                {{ $order->supplier->contact_number ?? 'Chưa cập nhật SĐT' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Thông tin Khoa / Phòng</h3>
                    <div class="flex items-start gap-4">
                        <div class="bg-amber-50 p-3 rounded-lg text-amber-600">
                            <span class="material-symbols-outlined text-2xl">apartment</span>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-800 dark:text-white mb-1">
                                {{ $order->department->department_name }}</p>
                            <p class="text-sm text-slate-500 mb-1 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">tag</span>
                                Mã: {{ $order->department->department_code }}
                            </p>
                            <p class="text-sm text-slate-500 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">person</span>
                                Liên hệ: {{ $order->department->contact_person ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th
                            class="px-8 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest w-16 text-center">
                            STT</th>
                        <th class="px-8 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest">Tên sản phẩm
                        </th>
                        <th class="px-8 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">
                            ĐVT</th>
                        <th class="px-8 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-center">Số
                            lượng</th>
                        <th class="px-8 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">Đơn
                            giá</th>
                        <th class="px-8 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">
                            Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($order->items as $index => $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                            <td class="px-8 py-4 text-sm text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="px-8 py-4">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                    {{ $item->product->product_name }}</p>
                            </td>
                            <td class="px-8 py-4 text-sm text-center text-slate-600">{{ $item->product->unit }}</td>
                            <td class="px-8 py-4 text-sm text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                            <td class="px-8 py-4 text-sm text-right font-mono text-slate-600">
                                {{ number_format($item->unit_price) }}</td>
                            <td class="px-8 py-4 text-sm text-right font-black font-mono text-indigo-600">
                                {{ number_format($item->total_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                        <td colspan="5"
                            class="px-8 py-4 text-sm font-bold text-slate-500 text-right uppercase tracking-widest">Tổng
                            tiền thanh toán</td>
                        <td class="px-8 py-4 text-xl font-black text-right font-mono text-indigo-600">
                            {{ number_format($order->total_amount) }} ₫</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection