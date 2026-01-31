@extends('layouts.admin')

@section('title', 'Duyệt Phiếu Tổng Hợp | Hệ Thống Vật Tư Y Tế')

@section('page-title', 'Duyệt Phiếu Tổng Hợp')

@push('styles')
<style type="text/tailwindcss">
    :root {
        --primary: #0d9488;
        --primary-hover: #0f766e;
        --bg-main: #f1f5f9;
        --secondary: #64748b;
    }
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .tab-active {
        @apply border-b-2 border-[var(--primary)] text-[var(--primary)] font-bold bg-slate-50 dark:bg-slate-800;
    }
    .tab-default {
        @apply text-slate-500 hover:text-[var(--primary)] hover:bg-slate-50 dark:hover:bg-slate-800 border-transparent transition-all;
    }
    .rejected-row {
        @apply opacity-50 bg-red-50 dark:bg-red-900/10 pointer-events-none;
    }
    .sticky-col-1 { @apply sticky left-0 z-20 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800; }
    .sticky-col-2 { @apply sticky left-[50px] z-20 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]; }
    .sticky-col-3 { @apply sticky left-[250px] z-20 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800; }
    
    .sticky-header { @apply sticky top-0 z-30 bg-slate-50 dark:bg-slate-800/90 backdrop-blur-sm; }
</style>
@endpush

@section('content')
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-6 -mx-8 -mt-8" 
    x-data="{ 
        activeTab: '{{ request('tab', 'general_table') }}',
        updateTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            // Preserve month and year filter parameters
            @if(request('month'))
                url.searchParams.set('month', '{{ request('month') }}');
            @endif
            @if(request('year'))
                url.searchParams.set('year', '{{ request('year') }}');
            @endif
            window.history.replaceState({}, '', url);
        }
    }">
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
        
        <!-- Month/Year Filter -->
        <form method="GET" action="{{ route('admin.approve_summary_votes') }}" class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700">
                <span class="material-symbols-outlined text-slate-400 text-[18px]">calendar_month</span>
                <select name="month" class="text-sm font-bold border-0 bg-transparent focus:ring-0 text-slate-700 dark:text-slate-200 pr-8">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                            Tháng {{ $m }}
                        </option>
                    @endfor
                </select>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <select name="year" class="text-sm font-bold border-0 bg-transparent focus:ring-0 text-slate-700 dark:text-slate-200 pr-8">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="ml-2 px-3 py-1.5 bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white rounded-md text-xs font-bold transition-all flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Lọc
                </button>
            </div>
        </form>
        
        <div class="flex items-center gap-3" x-data="{ openPending: false }">
            <!-- Pending List Button -->
            <div class="relative">
                <button @click="openPending = !openPending" class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-md shadow-md transition-all relative">
                    <span class="material-symbols-outlined text-[20px]">notifications_active</span>
                    <span class="text-sm">Yêu cầu chờ ({{ $pendingGroupedByDept->count() }})</span>
                    @if($pendingGroupedByDept->count() > 0)
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                    @endif
                </button>

                <!-- Popup -->
                <div x-show="openPending" 
                    @click.away="openPending = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 z-[100] overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Khoa/Phòng có yêu cầu</p>
                    </div>
                    <div class="max-h-80 overflow-y-auto custom-scrollbar">
                        @if($pendingGroupedByDept->count() > 0)
                            @foreach($departments as $dept)
                                @if($pendingGroupedByDept->has($dept->department_id))
                                    <button @click="updateTab('dept_{{ $dept->department_id }}'); openPending = false" 
                                        class="w-full px-4 py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-50 dark:border-slate-800 last:border-0">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 font-bold text-xs">
                                                {{ substr($dept->department_name, 0, 1) }}
                                            </div>
                                            <div class="text-left">
                                                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $dept->department_name }}</p>
                                                <p class="text-[10px] text-slate-400 font-medium">{{ $pendingGroupedByDept->get($dept->department_id)->count() }} mặt hàng</p>
                                            </div>
                                        </div>
                                        <span class="material-symbols-outlined text-slate-300 text-sm">chevron_right</span>
                                    </button>
                                @endif
                            @endforeach
                        @else
                            <div class="px-4 py-8 text-center">
                                <span class="material-symbols-outlined text-slate-200 text-4xl mb-2">check_circle</span>
                                <p class="text-xs font-bold text-slate-400">Không có yêu cầu chờ duyệt</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <button onclick="printAggregation()" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md shadow-md transition-all">
                <span class="material-symbols-outlined text-[20px]">print</span>
                <span class="text-sm">In</span>
            </button>
            <a href="{{ route('admin.aggregation.export_excel', ['month' => $currentMonth, 'year' => $currentYear]) }}" class="flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md shadow-md transition-all">
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span class="text-sm">Xuất Excel</span>
            </a>
             <form action="{{ route('admin.aggregation.process') }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Xác nhận duyệt và tạo PO?')" class="flex items-center gap-2 px-5 py-2.5 bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white font-bold rounded-md shadow-md transition-all">
                    <span class="material-symbols-outlined text-[20px]">task_alt</span>
                    <span class="text-sm">Duyệt & Tạo PO</span>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Navigation Tabs -->
    <div class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 sticky top-[56px] z-10 shadow-sm">
        <div class="px-6 overflow-x-auto custom-scrollbar">
            <div class="flex items-center gap-1 whitespace-nowrap">
                <button @click="updateTab('general_table')" 
                    :class="activeTab === 'general_table' ? 'tab-active' : 'tab-default'"
                    class="px-5 py-4 text-xs font-extrabold border-b-2 uppercase tracking-wide">
                    BẢNG TỔNG
                </button>
                <button @click="updateTab('aggregation')" 
                    :class="activeTab === 'aggregation' ? 'tab-active' : 'tab-default'"
                    class="px-5 py-4 text-xs font-extrabold border-b-2 uppercase tracking-wide">
                    TỔNG HỢP
                </button>
                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-2"></div>
                @foreach($departments as $dept)
                    <button @click="updateTab('dept_{{ $dept->department_id }}')" 
                        :class="activeTab === 'dept_{{ $dept->department_id }}' ? 'tab-active' : 'tab-default'"
                        class="px-5 py-4 text-xs font-bold border-b-2 uppercase tracking-wide flex items-center gap-1.5 relative">
                        {{ $dept->department_name }}
                        @if($pendingGroupedByDept->has($dept->department_id))
                            <span class="flex h-2 w-2 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="p-6 bg-slate-50 dark:bg-slate-900/50 min-h-[500px]">
        
        <!-- BẢNG TỔNG (All Items List) -->
        <!-- BẢNG TỔNG (Pivot Report - Products × Departments) -->
        <div x-show="activeTab === 'general_table'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            @if(isset($pivotData) && $pivotData->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-4">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-tight">Bảng tổng hợp Tháng {{ $currentBatch->batch_month ?? now()->month }}/{{ $currentBatch->batch_year ?? now()->year }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">Trạng thái: <span class="text-teal-600">{{ $currentBatch->status ?? 'ISSUED' }}</span></p>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar relative">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead>
                            <!-- TOP TOTALS ROW (User Request) -->
                            <tr class="bg-yellow-50/50 dark:bg-yellow-900/10 border-b border-slate-200 dark:border-slate-700">
                                <th class="sticky-col-1 py-2 bg-yellow-50/50 dark:bg-yellow-900/20"></th>
                                <th class="sticky-col-2 py-2 text-[10px] font-black text-slate-500 uppercase tracking-tighter text-right pr-4 bg-yellow-50/50 dark:bg-yellow-900/20">TỔNG SL YÊU CẦU:</th>
                                <th class="sticky-col-3 py-2 bg-yellow-50/50 dark:bg-yellow-900/20"></th>
                                @foreach($allDepartments as $dept)
                                <th class="px-3 py-2 text-[13px] font-black text-red-600 text-center border-l border-slate-200 dark:border-slate-700 font-mono">
                                    {{ number_format($deptTotalRequested[$dept->department_id] ?? 0, 0, ',', '.') }}
                                </th>
                                @endforeach
                                <th class="px-4 py-2 border-l-2 border-slate-200 bg-yellow-50/50 dark:bg-yellow-900/20"></th>
                            </tr>
                            
                            <!-- MAIN HEADERS -->
                            <tr class="sticky-header border-b border-slate-200 dark:border-slate-700">
                                <th class="sticky-col-1 px-4 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center" style="width: 50px;">STT</th>
                                <th class="sticky-col-2 px-4 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest" style="width: 200px;">Tên hàng hóa / Vật tư</th>
                                <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">ĐVT</th>
                                @foreach($allDepartments as $dept)
                                <th class="px-3 py-4 border-l border-slate-200 dark:border-slate-700 min-w-[80px]">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300 text-center leading-tight h-[30px] flex items-center justify-center">
                                            {{ $dept->department_name }}
                                        </div>
                                    </div>
                                </th>
                                @endforeach
                                <th class="px-4 py-4 text-[10px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest text-center border-l-2 border-blue-200 bg-blue-50/50" style="width: 100px;">TỔNG CỘNG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            @foreach($pivotData as $index => $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors group">
                                <td class="sticky-col-1 px-4 py-3 text-xs text-center text-slate-400 font-bold group-hover:bg-slate-50 dark:group-hover:bg-slate-800/40">{{ (($pivotPagination->currentPage() - 1) * $pivotPagination->perPage()) + $loop->iteration }}</td>
                                <td class="sticky-col-2 px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/40">{{ $row['product']->product_name }}</td>
                                <td class="px-4 py-3 text-xs text-center text-slate-500 font-medium group-hover:bg-slate-50 dark:group-hover:bg-slate-800/40">{{ $row['product']->unit }}</td>
                                @foreach($allDepartments as $dept)
                                    @php $qty = $row['departments'][$dept->department_id] ?? null; @endphp
                                <td class="px-3 py-3 text-sm text-center font-black {{ $qty ? 'text-slate-900 dark:text-white bg-green-50/30' : 'text-slate-300' }} border-l border-slate-100 dark:border-slate-800">
                                    {{ $qty ?: '-' }}
                                </td>
                                @endforeach
                                <td class="px-4 py-3 text-sm text-center font-black text-blue-800 dark:text-blue-400 border-l-2 border-blue-200 bg-blue-50/30">{{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-800 border-t-2 border-slate-200 dark:border-slate-700">
                            <tr>
                                <td class="sticky-col-1 px-4 py-4 bg-slate-50 dark:bg-slate-800"></td>
                                <td class="sticky-col-2 px-4 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right pr-4 bg-slate-50 dark:bg-slate-800">TỔNG SỐ LƯỢNG:</td>
                                <td class="sticky-col-3 px-4 py-4 bg-slate-50 dark:bg-slate-800"></td>
                                @foreach($allDepartments as $dept)
                                <td class="px-3 py-4 text-sm text-center font-black text-slate-900 dark:text-white border-l border-slate-200 dark:border-slate-700 font-mono">
                                    {{ number_format($deptQtyTotals[$dept->department_id] ?? 0, 0, ',', '.') }}
                                </td>
                                @endforeach
                                <td class="px-4 py-4 text-sm text-center font-black text-blue-800 dark:text-blue-400 border-l-2 border-blue-200 bg-blue-100/50 font-mono">
                                    {{ number_format($deptQtyTotals->sum(), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between bg-white dark:bg-slate-900 px-6 py-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Hiển thị <span class="text-slate-800">{{ $pivotPagination->firstItem() }}-{{ $pivotPagination->lastItem() }}</span> của <span class="text-[var(--primary)]">{{ $pivotPagination->total() }}</span> vật tư
                </div>
                <div class="pivot-pagination">
                    {{ $pivotPagination->links() }}
                </div>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                <span class="material-symbols-outlined text-6xl text-slate-300 mb-4 font-light">inventory_2</span>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">Chưa có dữ liệu tổng hợp (Trạng thái ISSUED)</p>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Bảng tổng sẽ hiển thị khi có đơn hàng được chuyển sang trạng thái "Đã giao" (ISSUED).</p>
            </div>
            @endif
        </div>

        <!-- TỔNG HỢP (Aggregated by Supplier) -->
        <div x-show="activeTab === 'aggregation'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
             @foreach($aggregatedBySupplier as $supplierId => $supplierItems)
                @php 
                    $supplierName = $supplierItems->first()->product->supplier->supplier_name ?? 'Chưa gán NCC';
                    $totalAmount = $supplierItems->sum(function($item) { return $item->total_approved * $item->product->unit_price; });
                @endphp
                
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                             <div class="bg-indigo-600 p-1.5 rounded-lg text-white">
                                <span class="material-symbols-outlined text-[18px]">storefront</span>
                            </div>
                            <h3 class="font-black text-slate-700 dark:text-slate-200 text-lg uppercase">{{ $supplierName }}</h3>
                        </div>
                        <span class="text-sm font-bold text-slate-500 font-mono">Tổng cộng: {{ number_format($totalAmount, 0, ',', '.') }} VNĐ</span>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[1100px]">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-16">STT</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Tên hàng hóa</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center">ĐVT</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center">Tổng SL</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right">Đơn giá</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right">Thành tiền</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest min-w-[320px]">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @php 
                                        $groupedByProduct = $supplierItems->groupBy('product_id');
                                        $stt = 1;
                                    @endphp
                                    @foreach($groupedByProduct as $prodId => $prods)
                                        @php
                                            $firstItem = $prods->first(); // This is now an AggregationItem from DB
                                            // AggregationItem stores the total for this product in this batch
                                            $prod = $firstItem->product;
                                            $totalQty = $firstItem->total_approved; // Use stored total logic 
                                            // Controller synced it, so total_requested is correct.
                                            
                                            $lineTotal = $totalQty * $prod->unit_price;
                                            $note = $firstItem->note ?? '';
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-4 text-sm text-center text-slate-400 font-medium">{{ $stt++ }}</td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $prod->product_name }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-center text-slate-600">{{ $prod->unit }}</td>
                                            <td class="px-6 py-4 text-sm text-center font-black text-emerald-600 bg-emerald-50 dark:bg-emerald-900/10 rounded">{{ $totalQty }}</td>
                                            <td class="px-6 py-4 text-sm text-right font-mono text-slate-600">{{ number_format($prod->unit_price, 0, ',', '.') }} VNĐ</td>
                                            <td class="px-6 py-4 text-sm text-right font-black font-mono text-slate-800">{{ number_format($lineTotal, 0, ',', '.') }} VNĐ</td>
                                            <td class="px-6 py-4" x-data="smartNote({{ $firstItem->aggregation_item_id }}, '{{ addslashes($note) }}')">
                                                <div class="relative" @click.away="dropdownOpen = false">
                                                    <!-- Note Input Area -->
                                                    <div class="relative group">
                                                        <input type="text" 
                                                            x-model="note"
                                                            @focus="dropdownOpen = true"
                                                            class="w-full text-sm font-bold border-slate-200 rounded-lg focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20 bg-white dark:bg-slate-800 transition-all pr-10 py-2.5 placeholder-slate-300 shadow-sm" 
                                                            placeholder="Nhập hoặc chọn mẫu...">
                                                        
                                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                                            <!-- Saving Indicator -->
                                                            <template x-if="saving">
                                                                <span class="material-symbols-outlined text-[18px] text-teal-500 animate-spin">sync</span>
                                                            </template>
                                                            <template x-if="!saving">
                                                                <span class="material-symbols-outlined text-[18px] text-slate-300 group-hover:text-[var(--primary)] transition-colors cursor-pointer" @click="dropdownOpen = !dropdownOpen">save</span>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- Smart Suggestions Dropdown -->
                                                    <div x-show="dropdownOpen" 
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="opacity-0 scale-95"
                                                        x-transition:enter-end="opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="opacity-100 scale-100"
                                                        x-transition:leave-end="opacity-0 scale-95"
                                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 p-2 min-w-[240px]">
                                                        
                                                        <div class="flex flex-wrap gap-1.5">
                                                            <template x-for="(sug, index) in suggestions" :key="index">
                                                                <div class="flex items-center group/chip">
                                                                    <button @click="addSuggestion(sug); dropdownOpen = false"
                                                                        type="button"
                                                                        :class="getChipClass(index)"
                                                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold transition-all border hover:bg-opacity-80">
                                                                        <span x-text="sug"></span>
                                                                    </button>
                                                                    <template x-if="settingsOpen">
                                                                        <button @click="removeSuggestion(index)" class="ml-1 text-red-400 hover:text-red-600">
                                                                            <span class="material-symbols-outlined text-[14px]">close</span>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            
                                                            <template x-if="settingsOpen">
                                                                <button @click="addNewSuggestion()" class="px-2 py-1 rounded-md text-[11px] font-bold border border-dashed border-slate-300 text-slate-400 hover:bg-slate-50 transition-all">
                                                                    + Thêm mẫu
                                                                </button>
                                                            </template>
                                                        </div>

                                                        <!-- Compact Settings Toggle -->
                                                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                                                            <button @click="toggleSettings()" class="text-[var(--primary)] hover:underline flex items-center gap-1">
                                                                <span class="text-[10px] font-bold">Thêm ghi chú nhanh</span>
                                                                <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100 dark:bg-slate-800">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-sm font-black text-slate-600 text-right uppercase tracking-wider">Tổng tiền thanh toán</td>
                                        <td class="px-6 py-4 text-sm font-black text-slate-800 font-mono text-right">{{ number_format($totalAmount, 0, ',', '.') }} VNĐ</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
             @endforeach
             
             @if($aggregatedBySupplier->isEmpty())
                <div class="flex flex-col items-center justify-center h-80 text-slate-300">
                    <span class="material-symbols-outlined text-6xl mb-4 opacity-50">content_paste_off</span>
                    <p class="font-medium">Không có dữ liệu tổng hợp</p>
                </div>
             @endif
        </div>

        <!-- Dynamic Department Tabs -->
        @foreach($departments as $dept)
            <div x-show="activeTab === 'dept_{{ $dept->department_id }}'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @php
                    // Show ALL items for this department (to see history)
                    $deptItems = $groupedByDept->get($dept->department_id);
                @endphp

                @if($deptItems)
                    @php
                        $itemsBySupplier = $deptItems->groupBy(function($item) {
                            return $item->product->supplier->supplier_name ?? 'Chưa gán NCC';
                        });
                        $totalAmount = 0;
                    @endphp

                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[var(--primary)]">apartment</span>
                            {{ $dept->department_name }}
                        </h2>
                        @php
                            $hasPendingItems = $deptItems->whereNull('decision_status')->where('decision_status', '!=', 'APPROVED')->where('decision_status', '!=', 'REJECTED')->isNotEmpty();
                            // Simplified check: since decision_status is null or 'PENDING' for items to be approved
                            $pendingCount = $deptItems->filter(function($item) {
                                return $item->decision_status === 'PENDING' || $item->decision_status === null;
                            })->count();
                        @endphp

                        @if($pendingCount > 0)
                            <form action="{{ route('admin.aggregation.approve_dept', $dept->department_id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn duyệt toàn bộ yêu cầu của khoa này và tạo PO?')">
                                @csrf
                                <button type="submit" class="flex items-center gap-1 px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 rounded-md transition-colors text-xs font-bold">
                                    <span class="material-symbols-outlined text-[16px]">done_all</span>
                                    Duyệt toàn bộ khoa & Tạo PO ({{ $pendingCount }})
                                </button>
                            </form>
                        @else
                            <div class="flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-500 rounded-md text-xs font-bold">
                                <span class="material-symbols-outlined text-[16px]">task_alt</span>
                                Đã xử lý tất cả
                            </div>
                        @endif
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-6">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[1100px]">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-16">STT</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Tên hàng hóa / Quy cách</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-28">ĐVT</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-28">Số lượng</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right w-40">Đơn giá</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right w-44">Thành tiền</th>
                                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center w-24">HÀNH ĐỘNG</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($itemsBySupplier as $supplierName => $items)
                                        <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                                            <td class="px-6 py-3" colspan="7">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-1 h-4 bg-[var(--primary)] rounded-full"></div>
                                                    <span class="text-xs font-black text-[var(--primary)] uppercase tracking-wider">{{ $supplierName }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach($items as $index => $item)
                                            @php 
                                                $lineTotal = $item->quantity_requested * $item->product->unit_price; 
                                                $totalAmount += $lineTotal;
                                            @endphp
                                            <tr id="row-{{ $item->request_item_id }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors {{ $item->decision_status == 'REJECTED' ? 'rejected-row' : '' }} {{ $item->decision_status == 'APPROVED' ? 'bg-green-50/50' : '' }}">
                                                <td class="px-6 py-4 text-sm text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $item->product->product_name }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-center font-medium text-slate-600">{{ $item->product->unit }}</td>
                                                <td class="px-6 py-4 text-sm text-center font-black text-red-500 bg-red-50 dark:bg-red-900/10">{{ $item->quantity_requested }}</td>
                                                <td class="px-6 py-4 text-sm text-right font-bold text-slate-600 font-mono">{{ number_format($item->product->unit_price, 0, ',', '.') }} VNĐ</td>
                                                <td class="px-6 py-4 text-sm text-right font-black text-slate-800 font-mono">{{ number_format($lineTotal, 0, ',', '.') }} VNĐ</td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        @if($item->decision_status == 'PENDING' || $item->decision_status == null)
                                                            <button onclick="approveItem({{ $item->request_item_id }})" class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 p-1.5 rounded-lg transition-all" title="Duyệt">
                                                                <span class="material-symbols-outlined text-[18px]">check</span>
                                                            </button>
                                                            <button onclick="rejectItem({{ $item->request_item_id }})" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg transition-all" title="Từ chối">
                                                                <span class="material-symbols-outlined text-[18px]">block</span>
                                                            </button>
                                                        @elseif($item->decision_status == 'APPROVED')
                                                            <span class="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-black uppercase rounded shadow-sm border border-green-200">Đã duyệt</span>
                                                        @elseif($item->decision_status == 'REJECTED')
                                                            <span class="px-2 py-1 bg-red-100 text-red-700 text-[10px] font-black uppercase rounded shadow-sm border border-red-200">Từ chối</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-[var(--primary)] text-white">
                                        <td class="px-6 py-4 text-sm font-black text-right uppercase tracking-widest" colspan="5">Tổng cộng</td>
                                        <td class="px-6 py-4 text-lg font-black text-right font-mono">{{ number_format($totalAmount, 0, ',', '.') }} VNĐ</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-80 text-slate-300">
                        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">content_paste_off</span>
                        <p class="font-medium">Không có phiếu yêu cầu nào từ {{ $dept->department_name }}</p>
                    </div>
                @endif
            </div>
        @endforeach

    </div>
</div>

@push('scripts')
<script>
    function approveItem(id) {
        // Optimistic UI update
        const rows = document.querySelectorAll(`#row-${id}`);
        rows.forEach(r => {
             r.classList.remove('rejected-row'); 
             r.querySelector('.text-red-500')?.classList.remove('opacity-50');
             r.classList.add('bg-green-50/50'); 
        });

        fetch(`/admin/request-items/${id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Feedback for user
                // Toast or visual indicator is better but user asked for "thông báo" (alert/notification)
                // We use a simple alert as requested, or rely on the visual change we already made + a small toast if we had a library.
                // Reverting to user expectation: "lúc trước ... có thông báo"
                 alert('Đã duyệt thành công!');
                 console.log('Approved item ' + id);
            } else {
                alert('Lỗi: ' + data.message);
                // Revert optimistic UI if failed
                rows.forEach(r => r.classList.remove('bg-green-50/50'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi kết nối.');
        });
    }

    function rejectItem(id) {
        if (!confirm('Bạn có chắc chắn muốn từ chối mục này không?')) return;
        
        const rows = document.querySelectorAll(`#row-${id}`);
        rows.forEach(r => r.classList.add('rejected-row'));
        
        fetch(`/admin/request-items/${id}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
             if (data.success) {
                 alert('Đã từ chối mục này.');
             } else {
                 alert('Lỗi: ' + data.message);
                 rows.forEach(r => r.classList.remove('rejected-row'));
             }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi kết nối.');
            rows.forEach(r => r.classList.remove('rejected-row'));
        });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('smartNote', (itemId, initialNote) => ({
            id: itemId,
            note: initialNote,
            saving: false,
            suggestions: [],
            settingsOpen: false,
            dropdownOpen: false,
            saveTimeout: null,
            
            init() {
                this.loadSuggestions();
                this.$watch('note', (value) => {
                    this.triggerAutoSave();
                });
            },

            loadSuggestions() {
                const saved = localStorage.getItem('smart_note_templates');
                if (saved) {
                    this.suggestions = JSON.parse(saved);
                } else {
                    this.suggestions = ["Hàng dễ vỡ", "Giao hỏa tốc 2h", "Kiểm tra tem niêm phong", "Khách VIP - Quà tặng", "Bọc chống sốc 3 lớp"];
                    this.saveLocalSuggestions();
                }
                
                // Sync with other instances
                window.addEventListener('suggestions-updated', () => {
                    this.suggestions = JSON.parse(localStorage.getItem('smart_note_templates'));
                });
            },

            saveLocalSuggestions() {
                localStorage.setItem('smart_note_templates', JSON.stringify(this.suggestions));
                window.dispatchEvent(new CustomEvent('suggestions-updated'));
            },

            triggerAutoSave() {
                if (this.saveTimeout) clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    this.saveToServer();
                }, 800);
            },

            async saveToServer() {
                this.saving = true;
                try {
                    const response = await fetch(`/admin/request-items/${this.id}/note`, {
                        method: 'POST',
                        body: JSON.stringify({ note: this.note }),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (!data.success) console.error('Auto-save failed:', data.message);
                } catch (e) {
                    console.error('Auto-save error:', e);
                } finally {
                    this.saving = false;
                }
            },

            addSuggestion(text) {
                if (!this.note) {
                    this.note = text;
                } else if (!this.note.includes(text)) {
                    this.note += ', ' + text;
                }
            },

            toggleSettings() {
                this.settingsOpen = !this.settingsOpen;
            },

            addNewSuggestion() {
                const text = prompt('Nhập mẫu ghi chú mới:');
                if (text && text.trim()) {
                    this.suggestions.push(text.trim());
                    this.saveLocalSuggestions();
                }
            },

            removeSuggestion(index) {
                if (confirm('Xóa mẫu này?')) {
                    this.suggestions.splice(index, 1);
                    this.saveLocalSuggestions();
                }
            },

            getChipClass(index) {
                const colors = [
                    'bg-indigo-50 border-indigo-100 text-indigo-600 hover:bg-indigo-100',
                    'bg-rose-50 border-rose-100 text-rose-600 hover:bg-rose-100',
                    'bg-amber-50 border-amber-100 text-amber-600 hover:bg-amber-100',
                    'bg-emerald-50 border-emerald-100 text-emerald-600 hover:bg-emerald-100',
                    'bg-sky-50 border-sky-100 text-sky-600 hover:bg-sky-100',
                    'bg-purple-50 border-purple-100 text-purple-600 hover:bg-purple-100'
                ];
                return colors[index % colors.length];
            }
        }));
    });

    // Keeping original functions for compatibility if needed elsewhere
    function updateNote(id, note) {
        return fetch(`/admin/request-items/${id}/note`, {
            method: 'POST',
            body: JSON.stringify({ note: note }),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    }

    function printAggregation() {
        // Fetch the print content
        fetch('{{ route("admin.aggregation.print") }}')
            .then(response => response.text())
            .then(html => {
                // Create a new window for printing
                const printWindow = window.open('', '_blank', 'width=800,height=600');
                printWindow.document.write(html);
                printWindow.document.close();
                
                // Wait for content to load then print
                printWindow.onload = function() {
                    printWindow.focus();
                    printWindow.print();
                    // Close window after printing (optional)
                    printWindow.onafterprint = function() {
                        printWindow.close();
                    };
                };
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Không thể tải nội dung in. Vui lòng thử lại.');
            });
    }
</script>
@endpush
@endsection
