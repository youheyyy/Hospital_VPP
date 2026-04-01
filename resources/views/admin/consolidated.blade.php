@extends('layouts.admin')

@section('title', 'Tổng hợp yêu cầu - Tháng ' . $selectedMonth)

{{-- body cần x-data cho Alpine --}}
@section('body-attrs', 'x-data="{ showNoteManager: false }"')

{{-- CSS và scripts đặc biệt --}}
@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style type="text/tailwindcss">
    .excel-table {
        @apply border-collapse w-full text-[13px];
    }
    .excel-table th,
    .excel-table td {
        @apply border border-slate-200 p-2;
    }
    .excel-table th {
        @apply bg-slate-50 font-bold text-center text-slate-600;
    }
    .category-header {
        @apply bg-indigo-600 text-white font-bold text-left uppercase text-[11px] tracking-wider;
    }
    .category-total {
        @apply bg-slate-50 font-bold;
    }
    .grand-total {
        @apply bg-amber-50 font-bold text-amber-900;
    }
    @media print {
        .no-print { display: none !important; }
        body { background: white; }
        .print-header { display: block !important; }
        .excel-table { font-size: 11px; }
        .excel-table th, .excel-table td { padding: 4px 6px; }
        @page { margin: 1.5cm; }
    }
    .print-header {
        display: none;
        text-align: center;
        margin-bottom: 20px;
    }
    .signature-section {
        display: none;
        margin-top: 30px;
    }
    @media print {
        .signature-section { display: flex !important; }
    }
    .cell-input {
        width: 100%;
        height: 100%;
        border: none;
        padding: 8px;
        text-align: right;
        outline: none;
        background: transparent;
        font-family: inherit;
        font-size: inherit;
        transition: all 0.2s ease-in-out;
    }
    .cell-input:focus {
        background-color: #f8fafc;
    }
    .saving-indicator {
        position: absolute;
        right: 4px;
        top: 4px;
        font-size: 10px;
        pointer-events: none;
    }
</style>
@endpush

{{-- Header: tiêu đề + controls --}}
@section('header-content')
    <div class="flex-shrink-0">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Tổng hợp yêu cầu VPP</h1>
        <p class="text-xs text-slate-400 font-medium">Tháng {{ $selectedMonth }} • {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        {{-- Smart Month Picker --}}
        <div class="relative" x-data="monthPicker('{{ $selectedMonth }}', '{{ request('category') }}')">
            <div class="flex items-center bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                <input type="text" x-model="displayMonth" @keydown.enter="submitMonth()"
                    @blur="formatAndSubmit()" placeholder="MM/YYYY"
                    class="w-24 px-3 py-2 text-xs border-none focus:ring-0 text-center font-bold text-slate-700"
                    maxlength="7">
                <button @click="showPicker = !showPicker"
                    class="px-2 py-2 hover:bg-slate-50 border-l border-slate-100 flex items-center text-slate-400">
                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                </button>
            </div>
            {{-- Month Picker Dropdown --}}
            <div x-show="showPicker" @click.away="showPicker = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute right-0 mt-2 p-4 bg-white border border-slate-200 shadow-2xl rounded-2xl z-50 w-64"
                style="display: none;">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-50">
                    <button @click="changeYear(-1)" class="p-1 hover:bg-slate-100 rounded-lg"><span class="material-symbols-outlined text-sm">chevron_left</span></button>
                    <span class="font-bold text-slate-900" x-text="pickerYear"></span>
                    <button @click="changeYear(1)" class="p-1 hover:bg-slate-100 rounded-lg"><span class="material-symbols-outlined text-sm">chevron_right</span></button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="m in 12">
                        <button @click="selectMonth(m)" class="py-2 text-xs rounded-xl transition-all"
                            :class="parseInt(pickerMonth) == m ? 'bg-indigo-600 text-white font-bold' : 'hover:bg-indigo-50 text-slate-600 font-medium'"
                            x-text="'Th ' + (m < 10 ? '0' + m : m)">
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <button onclick="printDirect()"
            class="px-3 py-2 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap">
            <span class="material-symbols-outlined text-sm">print</span>
            In
        </button>
        <button onclick="exportContextExcel()"
            class="px-3 py-2 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap">
            <span class="material-symbols-outlined text-sm">download</span>
            Xuất Excel
        </button>
        <a href="{{ route('admin.consolidated.export', ['month' => $selectedMonth]) }}"
            class="px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-2xl hover:bg-emerald-100 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap"
            title="Xuất tất cả dữ liệu ra 1 file nhiều sheets">
            <span class="material-symbols-outlined text-sm">table_chart</span>
            Tải File Tổng
        </a>
        <button onclick="exportToPDF()"
            class="px-3 py-2 bg-amber-500 text-white rounded-2xl hover:bg-amber-600 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap">
            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
            PDF
        </button>
    </div>
@endsection

{{-- Dùng content-class riêng: không padding, full width để bảng không bị cắt --}}
@section('content-class', 'flex-1 overflow-auto p-8')

@section('content')
    {{-- Hidden Print Iframe --}}
    <iframe id="printFrame" style="display:none;"></iframe>

    {{-- Tab Navigation --}}
    <div class="bg-white rounded-t-lg shadow-sm border border-b-0 border-gray-200 flex">
        <button onclick="switchTab('bang-tong')" id="tab-bang-tong"
            class="tab-button px-6 py-3 font-semibold text-sm border-b-2 border-blue-600 text-blue-600">
            BẢNG TỔNG
        </button>
        <button onclick="switchTab('tong-hop')" id="tab-tong-hop"
            class="tab-button px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700">
            TỔNG HỢP
        </button>
        <button onclick="switchTab('phieu-xuat-kho')" id="tab-phieu-xuat-kho"
            class="tab-button px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700">
            PHIẾU XUẤT KHO
        </button>
    </div>

    {{-- BẢNG TỔNG Tab --}}
    <div id="content-bang-tong"
        class="tab-content bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden">
        {{-- Dropdown Filter for Bảng Tổng --}}
        <div class="p-4 border-b bg-gray-50 flex items-center gap-4">
            <div class="relative flex-1 max-w-sm">
                <label for="searchBangTong" class="block text-xs font-medium text-gray-700 mb-1">Lọc theo sản phẩm:</label>
                <select id="searchBangTong" onchange="filterBangTong()"
                    class="w-full pl-3 pr-10 py-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                    <option value="">-- Tất cả sản phẩm --</option>
                    @php
                        $allUniqueProducts = collect();
                        foreach ($products as $catProducts) {
                            foreach ($catProducts as $p) {
                                if ($p->monthlyOrders->where('month', $selectedMonth)->sum('quantity') > 0) {
                                    $allUniqueProducts->push($p->name);
                                }
                            }
                        }
                        $allUniqueProducts = $allUniqueProducts->unique()->sort();
                    @endphp
                    @foreach($allUniqueProducts as $productName)
                        <option value="{{ $productName }}">{{ $productName }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 pt-5 text-gray-700">
                    <span class="material-symbols-outlined text-sm">expand_more</span>
                </div>
            </div>
        </div>

        <table class="excel-table" id="tableBangTong" x-data="consolidatedApp()">
            <thead>
                <tr>
                    <th style="width: 40px;">STT</th>
                    <th style="width: 250px;">TÊN HÀNG</th>
                    <th style="width: 80px;">ĐVT</th>
                    @foreach($departments as $dept)
                        <th style="width: 100px; background: #d4edda;">{{ mb_strtoupper($dept->name, 'UTF-8') }}</th>
                    @endforeach
                    <th style="width: 120px; background: #fff3cd;">Tổng SL</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($categories as $category)
                    @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                        {{-- Category Header --}}
                        <tr class="category-header">
                            <td colspan="{{ 4 + $departments->count() }}">{{ mb_strtoupper($category->name, 'UTF-8') }}</td>
                        </tr>
                        {{-- Products --}}
                        @foreach($products[$category->id] as $product)
                            @php
                                $stt++;
                                $monthlyOrders = $product->monthlyOrders->where('month', $selectedMonth);
                                $hasOrders = $monthlyOrders->sum('quantity') > 0;
                                $totalQuantity = 0;
                            @endphp
                            @if($hasOrders)
                                <tr>
                                    <td class="text-center text-gray-600">{{ $stt }}</td>
                                    <td class="font-medium">{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }}</td>
                                    @foreach($departments as $dept)
                                        @php
                                            $order = $monthlyOrders->firstWhere('department_id', $dept->id);
                                            $quantity = $order ? $order->quantity : 0;
                                            $totalQuantity += $quantity;
                                        @endphp
                                        <td class="relative p-0 h-full">
                                            <input type="text" value="{{ $quantity > 0 ? ($quantity + 0) : '' }}"
                                                @change="saveQuantity($event, '{{ $product->id }}', '{{ $dept->id }}')"
                                                class="cell-input {{ $quantity > 0 ? 'text-slate-900 font-semibold' : 'text-gray-400' }}"
                                                placeholder="">
                                            <div class="saving-indicator hidden">
                                                <span class="material-symbols-outlined text-[14px] text-emerald-500">check_circle</span>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="text-right font-semibold" style="background: #fff3cd;">
                                        {{ number_format($totalQuantity, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="3" class="text-right">TỔNG CỘNG SỐ LƯỢNG:</td>
                    @php $overallQty = 0; @endphp
                    @foreach($departments as $dept)
                        @php
                            $deptQty = 0;
                            foreach($categories as $cat) {
                                if(isset($products[$cat->id])) {
                                    foreach($products[$cat->id] as $p) {
                                        $deptQty += $p->monthlyOrders->where('month', $selectedMonth)->where('department_id', $dept->id)->sum('quantity');
                                    }
                                }
                            }
                            $overallQty += $deptQty;
                        @endphp
                        <td class="text-right">{{ $deptQty > 0 ? number_format($deptQty, 0, ',', '.') : '' }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($overallQty, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- TỔNG HỢP Tab --}}
    <div id="content-tong-hop"
        class="tab-content hidden bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden">
        {{-- Print Header --}}
        <div class="print-header p-6 text-[14px] leading-relaxed">
            <div class="flex justify-between items-start mb-4">
                <div class="text-center w-1/2">
                    <div class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH</div>
                    <div class="font-bold uppercase">BỘ PHẬN HỖ TRỢ DỊCH VỤ</div>
                </div>
                <div class="text-center w-1/2">
                    <div class="font-bold uppercase">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                    <div class="font-semibold flex flex-col items-center justify-center">
                        <span>Độc lập - Tự do - Hạnh phúc</span>
                        <span class="w-32 h-[1px] bg-black mt-0.5"></span>
                    </div>
                    <div class="italic mt-1"> Đồng Tháp, ngày <span class="print-date-day"></span> tháng
                        <span class="print-date-month"></span> năm <span class="print-date-year"></span>
                    </div>
                </div>
            </div>
            <div class="text-center mt-6">
                <div class="font-bold text-[18px] uppercase">BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO (BỆNH VIỆN)</div>
                <div class="font-semibold mt-1">Tháng {{ $selectedMonth }}</div>
            </div>
            <div class="mt-6 text-left">
                <p>- Căn cứ vào tình hình hoạt động thực tế tại đơn vị;</p>
                <p>- Căn cứ đề nghị các khoa/phòng tháng {{ $selectedMonth }} về thực tế nhu cầu sử dụng văn phòng phẩm vật tư tiêu hao hàng tháng trong phục vụ hoạt động chuyên môn của bệnh viện;</p>
                <p class="mt-2">Nay Bộ phận hỗ trợ dịch vụ kính trình Ban Giám Đốc phê duyệt mua VPP-VTTH tháng {{ $selectedMonth }}.</p>
            </div>
            <div class="mt-4">
                <div class="font-bold">Tổng số tiền: <span class="pdf-total-numeric"></span> đ</div>
                <div class="italic">Số tiền bằng chữ: <span class="pdf-total-text"></span></div>
            </div>
        </div>

        <table class="excel-table">
            <thead>
                <tr>
                    <th style="width: 40px;">STT</th>
                    <th style="width: 250px;">TÊN VPP - VTTH</th>
                    <th style="width: 80px;">ĐVT</th>
                    <th style="width: 80px;">SỐ LƯỢNG</th>
                    <th style="width: 120px;">ĐƠN GIÁ</th>
                    <th style="width: 130px;">THÀNH TIỀN</th>
                    <th style="width: 150px;" class="relative group pdf-hide">
                        <div class="flex items-center justify-between">
                            <span>GHI CHÚ QUẢN LÝ</span>
                            <button @click.stop="showNoteManager = !showNoteManager"
                                class="text-gray-400 hover:text-blue-600 p-1 rounded-full"
                                title="Quản lý ghi chú nhanh">
                                <span class="material-symbols-outlined text-[16px]">settings</span>
                            </button>
                        </div>
                        {{-- Quick Notes Manager Popover --}}
                        <div id="noteManager" x-show="showNoteManager" @click.away="showNoteManager = false"
                            x-data="{
                                suggestions: [],
                                newNote: '',
                                init() {
                                    this.load();
                                    window.addEventListener('suggestions-updated', () => this.load());
                                },
                                load() {
                                    const saved = localStorage.getItem('hospital_quick_notes');
                                    this.suggestions = saved ? JSON.parse(saved) : ['Cần gấp', 'Hàng thường xuyên', 'Mua theo yêu cầu', 'Đã đặt hàng', 'Chờ duyệt', 'Ưu tiên', 'Không gấp'];
                                },
                                save() {
                                    localStorage.setItem('hospital_quick_notes', JSON.stringify(this.suggestions));
                                    window.dispatchEvent(new CustomEvent('suggestions-updated'));
                                },
                                add() {
                                    const val = this.newNote.trim();
                                    if (val && !this.suggestions.includes(val)) {
                                        this.suggestions.push(val);
                                        this.save();
                                        this.newNote = '';
                                    }
                                },
                                remove(index) {
                                    this.suggestions.splice(index, 1);
                                    this.save();
                                }
                            }"
                            class="absolute right-0 top-full mt-1 w-64 bg-white border shadow-xl rounded-lg z-50 p-3 text-left"
                            style="display: none;">
                            <h4 class="text-sm font-bold text-gray-700 mb-2">Quản lý ghi chú nhanh</h4>
                            <div class="flex gap-1 mb-2">
                                <input type="text" x-model="newNote" @keydown.enter="add()"
                                    placeholder="Thêm ghi chú..."
                                    class="flex-1 text-xs border rounded px-2 py-1">
                                <button @click="add()" class="bg-blue-600 text-white px-2 rounded hover:bg-blue-700">
                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                </button>
                            </div>
                            <ul class="max-h-48 overflow-y-auto text-xs space-y-1">
                                <template x-for="(sug, index) in suggestions" :key="index">
                                    <li class="flex justify-between items-center group/item hover:bg-gray-50 p-1 rounded">
                                        <span class="truncate" x-text="sug"></span>
                                        <button @click="remove(index)"
                                            class="text-gray-400 hover:text-red-600 opacity-0 group-hover/item:opacity-100">
                                            <span class="material-symbols-outlined text-[14px]">close</span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($categories as $category)
                    @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                        <tr class="category-header">
                            <td colspan="7">{{ mb_strtoupper($category->name, 'UTF-8') }}</td>
                        </tr>
                        @foreach($products[$category->id] as $product)
                            @php
                                $stt++;
                                $monthlyOrders = $product->monthlyOrders->where('month', $selectedMonth);
                                $hasOrders = $monthlyOrders->sum('quantity') > 0;
                                $totalQuantity = 0;
                                $totalAmount = 0;
                            @endphp
                            @if($hasOrders)
                                <tr>
                                    <td class="text-center font-medium pdf-stt-cell">{{ $stt }}</td>
                                    <td class="font-medium px-4">{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }}</td>
                                    @php
                                        foreach ($departments as $dept) {
                                            $order = $monthlyOrders->firstWhere('department_id', $dept->id);
                                            $quantity = $order ? $order->quantity : 0;
                                            $totalQuantity += $quantity;
                                        }
                                        $totalAmount = $totalQuantity * $product->price;
                                    @endphp
                                    <td class="text-right font-bold">{{ number_format($totalQuantity, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-red-600">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                                    <td class="px-2 py-1 pdf-hide"
                                        x-data="smartNote('{{ $product->id }}', '{{ $selectedMonth }}', {{ \Illuminate\Support\Js::from($product->monthlyOrders->first()->admin_notes ?? '') }})">
                                        <div class="relative" @click.away="dropdownOpen = false">
                                            <div class="relative group">
                                                <input type="text" x-model="note" @focus="dropdownOpen = true"
                                                    :style="{ backgroundColor: bgColor }"
                                                    class="w-full border-gray-300 rounded text-sm px-2 py-1 transition-all pr-8"
                                                    placeholder="Nhập ghi chú...">
                                                <div class="absolute right-2 top-1/2 -translate-y-1/2">
                                                    <template x-if="saving">
                                                        <span class="material-symbols-outlined text-[16px] text-blue-500 animate-spin">sync</span>
                                                    </template>
                                                    <template x-if="!saving">
                                                        <span class="material-symbols-outlined text-[16px] text-gray-300 group-hover:text-blue-500 transition-colors cursor-pointer"
                                                            @click="dropdownOpen = !dropdownOpen">expand_more</span>
                                                    </template>
                                                </div>
                                            </div>
                                            {{-- Suggestions Dropdown --}}
                                            <div x-show="dropdownOpen" x-transition
                                                class="absolute z-50 mt-1 w-full bg-white border shadow-xl rounded-lg p-2 min-w-[200px]">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="(sug, index) in suggestions" :key="index">
                                                        <button @click="addSuggestion(sug); dropdownOpen = false"
                                                            type="button"
                                                            class="px-2 py-1 rounded text-[10px] font-bold border transition-all hover:bg-gray-100"
                                                            :class="getChipClass(index)" x-text="sug">
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        {{-- Category Total --}}
                        <tr class="category-total">
                            <td colspan="5" class="text-right">Cộng:</td>
                            @php
                                $catTotalAmount = 0;
                                foreach($products[$category->id] as $p) {
                                    $catTotalAmount += $p->monthlyOrders->where('month', $selectedMonth)->sum('quantity') * $p->price;
                                }
                            @endphp
                            <td class="text-right font-bold text-red-600">{{ number_format($catTotalAmount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach

                {{-- Total Quantity Row --}}
                @php
                    $totalOverallQty = 0;
                    foreach($categories as $cat) {
                        if(isset($products[$cat->id])) {
                            foreach($products[$cat->id] as $p) {
                                $totalOverallQty += $p->monthlyOrders->where('month', $selectedMonth)->sum('quantity');
                            }
                        }
                    }
                @endphp
                <tr class="category-total bg-amber-50">
                    <td colspan="3" class="text-right font-bold">TỔNG CỘNG SỐ LƯỢNG:</td>
                    <td class="text-right font-extrabold text-blue-700">{{ number_format($totalOverallQty, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
                {{-- Grand Total --}}
                <tr class="grand-total">
                    <td colspan="5" class="text-right text-lg">TỔNG CỘNG SỐ TIỀN:</td>
                    <td class="text-right text-lg">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Signature Section --}}
        <div class="signature-section justify-between p-12 mt-8 hidden">
            <div class="text-center">
                <div class="font-bold text-sm mb-20 uppercase">BP. HTDV</div>
                <div class="font-bold text-sm uppercase">Nguyễn Thị Thùy Trang</div>
            </div>
            <div class="text-center">
                <div class="font-bold text-sm mb-20 uppercase">TRƯỞNG PHÒNG TCKT</div>
                <div class="font-bold text-sm uppercase">Nguyễn Thị Thúy Huỳnh</div>
            </div>
            <div class="text-center">
                <div class="font-bold text-sm mb-20 uppercase">BAN GIÁM ĐỐC</div>
                <div class="font-bold text-sm uppercase">Huỳnh Thị Nguyệt</div>
            </div>
        </div>
    </div>

    {{-- PHIẾU XUẤT KHO Tab --}}
    <div id="content-phieu-xuat-kho"
        class="tab-content hidden bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden"
        x-data="stockIssueApp()">
        {{-- Controls --}}
        <div class="p-6 border-b bg-gray-50 no-print flex items-center justify-between">
            <div class="flex items-center gap-4">
                <label class="font-semibold text-gray-700">Chọn khoa/phòng:</label>
                <select x-model="selectedDeptId"
                    class="border-gray-300 rounded-lg text-sm px-4 py-2 min-w-[250px] shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <template x-for="dept in departments" :key="dept.id">
                        <option :value="dept.id" x-text="dept.name"></option>
                    </template>
                </select>
            </div>
        </div>
        {{-- Report Content --}}
        <div class="p-8">
            <table class="excel-table w-full">
                <thead>
                    <tr>
                        <th class="w-[50px] text-center">STT</th>
                        <th class="text-left">Tên hàng hóa, quy cách</th>
                        <th class="w-[80px] text-center">ĐVT</th>
                        <th class="w-[100px] text-center">Số lượng</th>
                        <th class="w-[120px] text-right">Đơn giá</th>
                        <th class="w-[150px] text-right">Thành tiền</th>
                        <th class="w-[150px]">Ghi chú</th>
                    </tr>
                </thead>
                <template x-for="(cat, index) in currentDeptData" :key="cat.id">
                    <tbody class="border-b border-gray-200">
                        <tr class="bg-blue-50">
                            <td colspan="7" class="text-left font-bold text-blue-800 italic border px-3 py-2"
                                x-text="romanize(index + 1) + '. ' + cat.name.toUpperCase()"></td>
                        </tr>
                        <template x-for="(prod, idx) in cat.products" :key="prod.id">
                            <tr class="hover:bg-gray-50">
                                <td class="text-center border px-3 py-2 text-gray-600" x-text="idx + 1"></td>
                                <td class="font-medium border px-3 py-2 text-left" x-text="prod.name"></td>
                                <td class="text-center border px-3 py-2" x-text="prod.unit"></td>
                                <td class="text-center font-bold text-red-600 border px-3 py-2" x-text="formatNumber(prod.quantity, 0)"></td>
                                <td class="text-right text-green-600 border px-3 py-2" x-text="formatNumber(prod.price, 0)"></td>
                                <td class="text-right font-bold text-green-700 border px-3 py-2" x-text="formatNumber(prod.total, 0)"></td>
                                <td class="border px-3 py-2 text-sm text-gray-500" x-text="prod.note"></td>
                            </tr>
                        </template>
                        <tr class="bg-blue-50">
                            <td colspan="5" class="text-right font-bold text-gray-700 border px-3 py-2">CỘNG NHÓM (<span x-text="romanize(index + 1)"></span>):</td>
                            <td class="text-right font-bold text-blue-700 border px-3 py-2" x-text="formatNumber(cat.total, 0)"></td>
                            <td class="border bg-white"></td>
                        </tr>
                    </tbody>
                </template>
                <tfoot>
                    <tr class="bg-gray-100">
                        <td colspan="5" class="text-right font-bold text-red-600 uppercase border px-3 py-3 text-lg">TỔNG CỘNG PHIẾU YÊU CẦU:</td>
                        <td class="text-right font-bold text-red-600 border px-3 py-3 text-lg" x-text="formatNumber(grandTotal, 0)"></td>
                        <td class="border w-[150px]"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
    function switchTab(tabName) {
        localStorage.setItem('active_consolidated_tab', tabName);
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('border-blue-600', 'text-blue-600');
            button.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById('content-' + tabName).classList.remove('hidden');
        const activeTab = document.getElementById('tab-' + tabName);
        if (activeTab) {
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-600', 'text-blue-600');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const savedTab = localStorage.getItem('active_consolidated_tab');
        if (savedTab) {
            switchTab(savedTab);
        } else {
            switchTab('bang-tong');
        }
    });

    function exportToExcel() {
        const table = document.querySelector('#content-tong-hop .excel-table');
        const wb = XLSX.utils.table_to_book(table, { sheet: "Tổng hợp" });
        XLSX.writeFile(wb, 'Tong_hop_VPP_{{ $selectedMonth }}.xlsx');
    }

    function getContextUrl(baseUrl) {
        const activeTab = localStorage.getItem('active_consolidated_tab') || 'bang-tong';
        let url = baseUrl + "?month=" + encodeURIComponent("{{ $selectedMonth }}");
        if (activeTab === 'bang-tong') {
            url += "&tabType=bang_tong";
        } else if (activeTab === 'tong-hop') {
            url += "&tabType=tong_hop";
        } else if (activeTab === 'phieu-xuat-kho') {
            url += "&tabType=phieu_xuat_kho";
            const selectElement = document.querySelector('select[x-model="selectedDeptId"]');
            if (selectElement) {
                url += "&deptId=" + encodeURIComponent(selectElement.value);
            }
        }
        return url;
    }

    function exportContextExcel() {
        window.location.href = getContextUrl("{{ route('admin.consolidated.export-single') }}");
    }

    function docSoThanhChu(number) {
        const chuSo = ["không", "một", "hai", "ba", "bốn", "năm", "sáu", "bảy", "tám", "chín"];
        const docBlock = (number) => {
            let tram = Math.floor(number / 100);
            let chuc = Math.floor((number % 100) / 10);
            let donvi = number % 10;
            let res = "";
            if (tram > 0) res += chuSo[tram] + " trăm ";
            else if (res !== "") res += "không trăm ";
            if (chuc > 1) res += chuSo[chuc] + " mươi ";
            else if (chuc === 1) res += "mười ";
            else if (tram > 0 && donvi > 0) res += "lẻ ";
            if (donvi === 5 && chuc >= 1) res += "lăm";
            else if (donvi > 1 || (donvi === 1 && chuc === 0)) res += chuSo[donvi];
            else if (donvi === 1 && chuc > 0) res += "mốt";
            return res;
        };
        const hangDonVi = ["", " nghìn", " triệu", " tỷ", " nghìn tỷ", " triệu tỷ"];
        if (number === 0) return "Không đồng";
        let res = "", i = 0;
        do {
            let block = number % 1000;
            if (block > 0) {
                let s = docBlock(block);
                res = s + hangDonVi[i] + (res !== "" ? " " : "") + res;
            }
            i++;
            number = Math.floor(number / 1000);
        } while (number > 0);
        return res.trim().charAt(0).toUpperCase() + res.trim().slice(1) + " đồng./.";
    }

    async function exportToPDF() {
        const month = "{{ $selectedMonth }}";
        const printUrl = getContextUrl("{{ route('admin.consolidated.print') }}");
        const activeTab = localStorage.getItem('active_consolidated_tab') || 'bang-tong';
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Đang chuẩn bị PDF...';
        btn.disabled = true;
        try {
            const response = await fetch(printUrl);
            const html = await response.text();
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const styles = tempDiv.querySelectorAll('style');
            const container = document.createElement('div');
            styles.forEach(style => { container.appendChild(style.cloneNode(true)); });
            const pages = tempDiv.querySelectorAll('.page');
            pages.forEach(p => {
                const clone = p.cloneNode(true);
                clone.style.marginBottom = '20px';
                container.appendChild(clone);
            });
            const opt = {
                margin: [0.2, 0.2, 0.2, 0.2],
                filename: 'In_VPP_' + month.replace('/', '_') + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, letterRendering: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: activeTab === 'bang-tong' ? 'landscape' : 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
            };
            await html2pdf().set(opt).from(container).save();
        } catch (error) {
            console.error("PDF generation failed:", error);
            alert("Có lỗi xảy ra khi tạo PDF. Vui lòng thử lại.");
        } finally {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
    }

    function printDirect() {
        const printUrl = getContextUrl("{{ route('admin.consolidated.print') }}");
        const printFrame = document.getElementById('printFrame');
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Đang chuẩn bị...';
        btn.disabled = true;
        printFrame.onload = function () {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch (e) {
                console.error("Print failed:", e);
                window.open(printUrl, '_blank');
            } finally {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        };
        printFrame.src = printUrl;
    }

    function filterBangTong() {
        const select = document.getElementById("searchBangTong");
        const filter = select.value.toUpperCase();
        const table = document.getElementById("tableBangTong");
        const tr = table.getElementsByTagName("tr");
        let currentCategoryRow = null;
        let hasVisibleProductsInCategory = false;
        for (let i = 0; i < tr.length; i++) {
            const row = tr[i];
            if (row.parentElement.tagName === 'THEAD') continue;
            if (row.classList.contains("category-header")) {
                if (currentCategoryRow && !hasVisibleProductsInCategory) {
                    currentCategoryRow.style.display = "none";
                }
                currentCategoryRow = row;
                hasVisibleProductsInCategory = false;
                row.style.display = "";
            } else {
                const td = row.getElementsByTagName("td")[1];
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    if (filter === "" || txtValue.toUpperCase() === filter) {
                        row.style.display = "";
                        hasVisibleProductsInCategory = true;
                    } else {
                        row.style.display = "none";
                    }
                }
            }
        }
        if (currentCategoryRow && !hasVisibleProductsInCategory) {
            currentCategoryRow.style.display = "none";
        }
    }
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('smartNote', (productId, month, initialNote) => ({
            productId: productId,
            month: month,
            note: initialNote,
            saving: false,
            bgColor: 'transparent',
            suggestions: [],
            dropdownOpen: false,
            saveTimeout: null,

            init() {
                this.loadSuggestions();
                this.$watch('note', (value, oldValue) => {
                    if (value !== oldValue) { this.triggerAutoSave(); }
                });
                window.addEventListener('suggestions-updated', () => this.loadSuggestions());
            },

            loadSuggestions() {
                const saved = localStorage.getItem('hospital_quick_notes');
                this.suggestions = saved ? JSON.parse(saved) : ['Cần gấp', 'Hàng thường xuyên', 'Mua theo yêu cầu', 'Đã đặt hàng', 'Chờ duyệt', 'Ưu tiên', 'Không gấp'];
            },

            triggerAutoSave() {
                if (this.saveTimeout) clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => { this.saveToServer(); }, 800);
            },

            async saveToServer() {
                this.saving = true;
                this.bgColor = '#f3f4f6';
                try {
                    const response = await fetch('{{ route("admin.consolidated.update_note") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ product_id: this.productId, month: this.month, note: this.note })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.bgColor = '#d1fae5';
                        setTimeout(() => { this.bgColor = 'transparent'; }, 800);
                    } else {
                        this.bgColor = '#fee2e2';
                    }
                } catch (e) {
                    this.bgColor = '#fee2e2';
                } finally {
                    this.saving = false;
                }
            },

            addSuggestion(text) {
                if (!this.note) { this.note = text; }
                else if (!this.note.includes(text)) { this.note += ', ' + text; }
            },

            getChipClass(index) {
                const colors = [
                    'bg-blue-50 border-blue-100 text-blue-600',
                    'bg-green-50 border-green-100 text-green-600',
                    'bg-yellow-50 border-yellow-100 text-yellow-600',
                    'bg-purple-50 border-purple-100 text-purple-600',
                    'bg-pink-50 border-pink-100 text-pink-600',
                    'bg-indigo-50 border-indigo-100 text-indigo-600'
                ];
                return colors[index % colors.length];
            }
        }));
    });
</script>

@php
$jsonDepartments = $departments->map(function ($dept) {
    return ['id' => $dept->id, 'name' => $dept->name];
});

$deptData = [];
foreach ($departments as $dept) {
    $deptCats = [];
    foreach ($categories as $cat) {
        $catProducts = [];
        if (isset($products[$cat->id])) {
            foreach ($products[$cat->id] as $product) {
                $matchingOrders = $product->monthlyOrders
                    ->where('department_id', $dept->id)
                    ->where('month', $selectedMonth);
                $totalQty = $matchingOrders->sum('quantity');
                if ($totalQty > 0) {
                    $catProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'quantity' => $totalQty,
                        'price' => $product->price,
                        'total' => $totalQty * $product->price,
                        'note' => $matchingOrders->pluck('notes')->filter()->implode('; ')
                    ];
                }
            }
        }
        if (count($catProducts) > 0) {
            $deptCats[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'products' => $catProducts,
                'total' => array_sum(array_column($catProducts, 'total'))
            ];
        }
    }
    $deptData[$dept->id] = $deptCats;
}
@endphp

<script>
    function consolidatedApp() {
        return {
            saveQuantity(event, productId, deptId) {
                let input = event.target;
                let value = input.value;
                let cell = input.closest('td');
                let indicator = cell.querySelector('.saving-indicator');
                input.classList.add('bg-indigo-50');
                fetch('{{ route("admin.consolidated.update-quantity") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        department_id: deptId,
                        month: '{{ $selectedMonth }}',
                        quantity: value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (value > 0) {
                            input.classList.remove('text-gray-400');
                            input.classList.add('text-slate-900', 'font-semibold');
                        } else {
                            input.classList.remove('text-slate-900', 'font-semibold');
                            input.classList.add('text-gray-400');
                        }
                        indicator.classList.remove('hidden');
                        setTimeout(() => {
                            indicator.classList.add('hidden');
                            input.classList.remove('bg-indigo-50');
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    input.classList.add('bg-red-50');
                    alert('Lỗi lưu dữ liệu. Vui lòng thử lại.');
                });
            }
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('stockIssueApp', () => ({
            departments: @json($jsonDepartments),
            allDeptData: @json($deptData),
            selectedDeptId: null,
            selectedDeptName: '',
            currentDeptData: [],
            grandTotal: 0,

            init() {
                if (this.departments.length > 0) {
                    this.selectedDeptId = this.departments[0].id;
                }
                this.$watch('selectedDeptId', (val) => this.updateView(val));
                if (this.selectedDeptId) { this.updateView(this.selectedDeptId); }
            },

            updateView(deptId) {
                const dept = this.departments.find(d => d.id == deptId);
                this.selectedDeptName = dept ? dept.name : '';
                this.currentDeptData = this.allDeptData[deptId] || [];
                this.grandTotal = 0;
                this.currentDeptData.forEach(cat => { this.grandTotal += cat.total; });
            },

            formatNumber(num, decimals = 0) {
                return new Intl.NumberFormat('vi-VN', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }).format(num);
            },

            romanize(num) {
                if (isNaN(num)) return NaN;
                var digits = String(+num).split(""),
                    key = ["", "C", "CC", "CCC", "CD", "D", "DC", "DCC", "DCCC", "CM",
                        "", "X", "XX", "XXX", "XL", "L", "LX", "LXX", "LXXX", "XC",
                        "", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX"],
                    roman = "", i = 3;
                while (i--)
                    roman = (key[+digits.pop() + (i * 10)] || "") + roman;
                return Array(+digits.join("") + 1).join("M") + roman;
            }
        }));
    });

    function monthPicker(initialMonth, selectedCategory) {
        let [m, y] = initialMonth.split('/');
        return {
            displayMonth: initialMonth,
            pickerMonth: m,
            pickerYear: y,
            showPicker: false,
            category: selectedCategory,
            changeYear(dir) { this.pickerYear = parseInt(this.pickerYear) + dir; },
            selectMonth(m) {
                this.pickerMonth = m < 10 ? '0' + m : m;
                this.submitMonth();
            },
            formatAndSubmit() {
                if (/^\d{1,2}\/\d{4}$/.test(this.displayMonth)) {
                    let parts = this.displayMonth.split('/');
                    let mm = parts[0].padStart(2, '0');
                    this.displayMonth = mm + '/' + parts[1];
                    this.submitMonth();
                }
            },
            submitMonth() {
                let finalMonth = this.displayMonth;
                if (this.showPicker) { finalMonth = this.pickerMonth + '/' + this.pickerYear; }
                let url = "{{ route('admin.consolidated') }}?month=" + encodeURIComponent(finalMonth);
                if (this.category) { url += "&category=" + encodeURIComponent(this.category); }
                window.location.href = url;
            }
        }
    }
</script>
@endpush