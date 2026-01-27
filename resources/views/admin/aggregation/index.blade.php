@extends('layouts.admin')

@section('title', 'Tổng hợp Nhu cầu Mua sắm')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Tổng hợp Nhu cầu</h1>
                <p class="text-slate-500 text-sm mt-1">Xem và tạo đơn hàng (PO) từ các yêu cầu đã được duyệt từ các khoa
                    phòng.</p>
            </div>

            @if($aggregated->isNotEmpty())
                <form action="{{ route('admin.aggregation.process') }}" method="POST">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Hệ thống sẽ tự động tạo các đơn hàng (PO) tách theo từng Khoa và Nhà cung cấp. Bạn có chắc chắn muốn tiếp tục?')"
                        class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-lg font-bold hover:bg-sky-700 shadow-lg shadow-sky-500/20 active:scale-95 transition-all">
                        <span class="material-symbols-outlined">playlist_add_check</span>
                        TẠO ĐƠN HÀNG (PO)
                    </button>
                </form>
            @endif
        </div>

        <!-- Alert / Info -->
        <div
            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-500 mt-0.5">info</span>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-bold mb-1">Cơ chế hoạt động:</p>
                <ul class="list-disc pl-4 space-y-1 opacity-90">
                    <li>Các mặt hàng giống nhau sẽ được gom nhóm để bạn dễ theo dõi tổng số lượng.</li>
                    <li>Khi bấm <strong>Tạo Đơn Hàng</strong>, hệ thống sẽ tự động tách PO theo từng
                        <strong>Khoa/Phòng</strong> và <strong>Nhà cung cấp</strong>.
                    </li>
                    <li>Mã PO sẽ được tạo theo định dạng: <code>PO_YYYY_MM_SEQ</code>.</li>
                </ul>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead
                        class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Sản phẩm</th>
                            <th class="px-6 py-4 text-center">ĐVT</th>
                            <th class="px-6 py-4 text-center">Tổng Số Lượng</th>
                            <th class="px-6 py-4">Nhà cung cấp</th>
                            <th class="px-6 py-4">Chi tiết yêu cầu (Khoa: SL)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($aggregated as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $item['product']->product_name }}
                                    </div>
                                    <div class="text-xs text-slate-400">SKU: {{ $item['product']->product_code ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm">{{ $item['product']->unit }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center justify-center h-8 px-3 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 font-bold">
                                        {{ $item['total_quantity'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-slate-400 text-sm">store</span>
                                        {{ $item['supplier']->supplier_name ?? 'Chưa gán' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item['items'] as $reqItem)
                                            <div
                                                class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded px-2 py-1 text-xs">
                                                <span
                                                    class="font-bold text-slate-700 dark:text-slate-300">{{ $reqItem->request->department->department_name }}</span>
                                                <span class="text-slate-400">|</span>
                                                <span class="font-mono font-bold text-primary">{{ $reqItem->quantity }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <span class="material-symbols-outlined text-5xl opacity-50">inbox</span>
                                        <span>Hiện không có yêu cầu nào đang chờ xử lý.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection