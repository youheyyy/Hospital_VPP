<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tổng hợp yêu cầu - Tháng {{ $selectedMonth }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#1e1b4b',
                        },
                        mint: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            400: '#94a3b8',
                            500: '#64748b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ["Plus Jakarta Sans", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body { @apply bg-slate-50 text-slate-900 antialiased font-sans; }
        }

        .excel-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-size: 13px;
            table-layout: fixed;
        }

        .excel-table th,
        .excel-table td {
            @apply border border-slate-200 p-2 relative;
        }

        /* Sticky main header - sticks below the pageHeader */
        .excel-table thead th {
            position: sticky;
            top: var(--table-top-offset, 0px);
            z-index: 100;
            background-color: #f8fafc;
            box-shadow: inset 0 -1px 0 rgba(0,0,0,0.1);
        }

        /* Sticky category header - sticks below thead */
        .excel-table tr.category-header td {
            position: sticky;
            z-index: 90;
            background-color: #4f46e5 !important;
            color: white !important;
            box-shadow: inset 0 -1px 0 rgba(0,0,0,0.1);
            top: var(--table-category-offset, 80px);
        }

        /* Sticky columns - keep background white */
        .excel-table th:nth-child(1),
        .excel-table td:nth-child(1) {
            position: sticky !important;
            left: 0;
            z-index: 85;
            background-color: white;
            min-width: 40px;
        }

        .excel-table th:nth-child(2),
        .excel-table td:nth-child(2) {
            position: sticky !important;
            left: 40px;
            z-index: 85;
            background-color: white;
            min-width: 250px;
        }

        .excel-table th:nth-child(3),
        .excel-table td:nth-child(3) {
            position: sticky !important;
            left: 290px;
            z-index: 85;
            background-color: white;
            min-width: 80px;
        }

        /* Intersections: Header cells that are ALSO sticky columns */
        .excel-table thead th:nth-child(1) { z-index: 120 !important; left: 0; }
        .excel-table thead th:nth-child(2) { z-index: 120 !important; left: 40px; }
        .excel-table thead th:nth-child(3) { z-index: 120 !important; left: 290px; }
        
        /* Category junctions */
        .excel-table tr.category-header td:nth-child(1) { z-index: 110 !important; }

        /* Overlapping headers (top-left intersection) - highest z-index */
        .excel-table thead th:nth-child(1) { z-index: 110; left: 0; background-color: #f8fafc !important; }
        .excel-table thead th:nth-child(2) { z-index: 110; left: 40px; background-color: #f8fafc !important; }
        .excel-table thead th:nth-child(3) { z-index: 110; left: 290px; background-color: #f8fafc !important; }

        /* Fix backgrounds for sticky cells to prevent transparency */
        .excel-table tbody td { background-color: white; }
        .excel-table tr.product-row td { background-color: white; }
        .excel-table tr.product-row:hover td { background-color: #f1f5f9; }
        .excel-table thead th:nth-child(n+4) { background-color: #d4edda; }

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

        .bento-card {
            @apply bg-white rounded-[2rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] p-6 transition-all duration-300 hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.08)];
        }

        .sidebar-item {
            @apply flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200 cursor-pointer;
        }

        .sidebar-item.active {
            @apply bg-indigo-600 text-white shadow-lg shadow-indigo-200;
        }

        .sidebar-item .material-symbols-outlined {
            @apply text-2xl;
        }

        .cell-input {
            width: 100%;
            min-width: 0;
            height: 100%;
            border: none;
            padding: 8px;
            text-align: right;
            outline: none;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;
        }
        .cell-input:focus {
            background-color: #f8fafc;
            /* removed the inset box-shadow to prevent it from looking like a grid */
        }
        .saving-indicator {
            position: absolute;
            right: 4px;
            top: 4px;
            font-size: 10px;
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-slate-50" x-data="{ showNoteManager: false }">
    <div class="flex h-screen">
        <!-- Hidden Print Iframe -->
        <iframe id="printFrame" style="display:none;"></iframe>
        <!-- Sidebar -->
        <aside
            class="w-64 flex-shrink-0 bg-white border-r border-slate-100 flex flex-col no-print transition-all duration-300">
            <div class="p-6 border-b border-slate-100">
                <div class="flex flex-col items-center justify-center gap-3 w-full pt-2">
                    <img src="{{ asset('images/logo-tmmc.png') }}" alt="Logo" class="h-20 w-auto object-contain">
                    <div class="flex items-center justify-center gap-1.5 w-full">
                        <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                        <span
                            class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.15em] whitespace-nowrap">Quản
                            Lý Văn Phòng Phẩm</span>
                        <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a class="sidebar-item" href="{{ route('admin.dashboard') }}">
                    <span class="material-symbols-outlined">grid_view</span>
                    <span class="text-sm font-bold">Tổng quan</span>
                </a>
                <a class="sidebar-item active" href="{{ route('admin.consolidated') }}">
                    <span class="material-symbols-outlined">assignment</span>
                    <span class="text-sm font-bold">Tổng hợp yêu cầu</span>
                </a>
                <a class="sidebar-item" href="{{ route('admin.products') }}">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="text-sm font-bold">Quản lý sản phẩm VPP</span>
                </a>
            </nav>
            <div class="p-4 border-t border-slate-100">
                <div class="bg-slate-50 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shadow-sm">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2, 'UTF-8'), 'UTF-8') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 uppercase truncate">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit"
                            class="w-full text-xs font-bold text-slate-500 hover:text-indigo-600 text-left px-2 py-1 transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">logout</span>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-auto" id="mainContent">
            <!-- Header -->
            <header id="pageHeader"
                class="bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center gap-3 no-print min-w-0 sticky top-0 left-0 z-[150]">
                <div class="flex-shrink-0">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Tổng hợp yêu cầu VPP</h1>
                    <p class="text-xs text-slate-400 font-medium">Tháng {{ $selectedMonth }} •
                        {{ now()->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <!-- Smart Month Picker -->
                    <div class="relative" x-data="monthPicker('{{ $selectedMonth }}', '{{ request('category') }}')">
                        <div
                            class="flex items-center bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                            <input type="text" x-model="displayMonth" @keydown.enter="submitMonth()"
                                @blur="formatAndSubmit()" placeholder="MM/YYYY"
                                class="w-24 px-3 py-2 text-xs border-none focus:ring-0 text-center font-bold text-slate-700"
                                maxlength="7">
                            <button @click="showPicker = !showPicker"
                                class="px-2 py-2 hover:bg-slate-50 border-l border-slate-100 flex items-center text-slate-400">
                                <span class="material-symbols-outlined text-sm">calendar_month</span>
                            </button>
                        </div>

                        <!-- Month Picker Dropdown -->
                        <div x-show="showPicker" @click.away="showPicker = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute right-0 mt-2 p-4 bg-white border border-slate-200 shadow-2xl rounded-2xl z-50 w-64"
                            style="display: none;">

                            <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-50">
                                <button @click="changeYear(-1)" class="p-1 hover:bg-slate-100 rounded-lg"><span
                                        class="material-symbols-outlined text-sm">chevron_left</span></button>
                                <span class="font-bold text-slate-900" x-text="pickerYear"></span>
                                <button @click="changeYear(1)" class="p-1 hover:bg-slate-100 rounded-lg"><span
                                        class="material-symbols-outlined text-sm">chevron_right</span></button>
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
                    <a href="{{ route('admin.consolidated.export.biemmau', ['month' => $selectedMonth]) }}"
                        class="px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-2xl hover:bg-emerald-100 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap"
                        title="Xuất chi tiết biểu mẫu, cuốn/tờ và quy đổi gram">
                        <span class="material-symbols-outlined text-sm">description</span>
                        Excel Biểu mẫu
                    </a>
                    <!-- <a href="{{ route('admin.consolidated.export.tongvpp', ['month' => $selectedMonth]) }}"
                        class="px-3 py-2 bg-violet-50 text-violet-700 border border-violet-200 rounded-2xl hover:bg-violet-100 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap"
                        title="Xuất gộp biểu mẫu thành Gram giấy, không hiển thị chi tiết">
                        <span class="material-symbols-outlined text-sm">summarize</span>
                        Excel Tổng VPP
                    </a> -->
                    <a href="{{ route('admin.consolidated.export.tongvppall', ['month' => $selectedMonth]) }}"
                        class="px-3 py-2 bg-sky-100 text-sky-700 border border-sky-300 rounded-2xl hover:bg-sky-200 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap"
                        title="Xuất gộp biểu mẫu thành Gram giấy trên tất cả các trang, bao gồm Bảng Tổng">
                        <span class="material-symbols-outlined text-sm">grid_view</span>
                        Excel Tổng VPP Chung
                    </a>
                    <button onclick="exportToPDF()"
                        class="px-3 py-2 bg-amber-500 text-white rounded-2xl hover:bg-amber-600 flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                        PDF
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-shrink-0 flex flex-col bg-slate-50 px-8 pt-4">
                <!-- Tab Navigation -->
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

                <!-- BẢNG TỔNG Tab -->
                <div id="content-bang-tong" x-data="consolidatedApp()"
                    class="tab-content flex flex-col bg-white rounded-b-lg shadow-sm border border-gray-200 relative mb-8">
                    <!-- Dropdown Filter for Bảng Tổng (non-scrolling) -->
                    <div class="p-4 border-b bg-gray-50 flex items-center gap-4">
                        <div class="relative flex-1 max-w-sm">
                            <label for="searchBangTong" class="block text-xs font-medium text-gray-700 mb-1">Lọc theo
                                sản phẩm:</label>
                            <select id="searchBangTong" onchange="filterBangTong()"
                                class="w-full pl-3 pr-10 py-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                                <option value="">-- Tất cả sản phẩm --</option>
                                @php
                                $allUniqueProducts = collect();
                                foreach ($products as $catProducts) {
                                foreach ($catProducts as $p) {
                                $allUniqueProducts->push($p->name);
                                }
                                }
                                $allUniqueProducts = $allUniqueProducts->unique()->sort();
                                @endphp
                                @foreach($allUniqueProducts as $productName)
                                <option value="{{ $productName }}">{{ $productName }}</option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 pt-5 text-gray-700">
                                <span class="material-symbols-outlined text-sm">expand_more</span>
                            </div>
                        </div>

                        <div class="relative flex-1 max-w-xs">
                            <label for="viewModeBangTong" class="block text-xs font-medium text-gray-700 mb-1">Chế độ
                                hiển thị:</label>
                            <select id="viewModeBangTong" onchange="filterBangTong()"
                                class="w-full pl-3 pr-10 py-2 border-indigo-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-white font-medium text-indigo-700">
                                <option value="requested" selected>Sản phẩm có yêu cầu</option>
                                <option value="all">Toàn bộ danh mục (Tất cả)</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 pt-5 text-indigo-700">
                                <span class="material-symbols-outlined text-sm">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container: Removed overflow div so horizontal scroll is handled by mainContent -->
                    <div class="w-full">
                        <table class="excel-table" id="tableBangTong">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">STT</th>
                                    <th style="width: 250px;">TÊN HÀNG</th>
                                    <th style="width: 80px;">ĐVT</th>
                                    @foreach($departments as $dept)
                                    <th
                                        style="width: 60px; max-width: 60px; background: #d4edda; word-break: break-word; overflow-wrap: break-word; white-space: normal;">
                                        {{ mb_strtoupper($dept->name, 'UTF-8') }}
                                    </th>
                                    @endforeach
                                    <th style="width: 120px; background: #fff3cd;">Tổng SL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $stt = 0; @endphp
                                @foreach($categories as $category)
                                @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                                <!-- Category Header -->
                                <tr class="category-header">
                                    <td colspan="{{ 4 + $departments->count() }}">
                                        <span class="sticky left-0 pl-1 inline-block">{{ mb_strtoupper($category->name,
                                            'UTF-8') }}</span>
                                    </td>
                                </tr>

                                <!-- Products -->
                                @foreach($products[$category->id] as $product)
                                @php
                                // Correctly filter orders for this month only
                                $monthlyOrders = $product->monthlyOrders->where('month', $selectedMonth);
                                $hasOrders = $monthlyOrders->sum('quantity') > 0;
                                $totalQuantity = 0;
                                @endphp
                                <tr class="product-row {{ $hasOrders ? 'has-orders' : 'no-orders opacity-60' }}"
                                    data-has-orders="{{ $hasOrders ? 'true' : 'false' }}"
                                    style="{{ $hasOrders ? '' : 'display: none;' }}">
                                    <td class="text-center text-gray-600 row-stt"></td>
                                    <td class="font-medium">{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }} </td>
                                    @foreach($departments as $dept)
                                    @php
                                    $order = $monthlyOrders->firstWhere('department_id', $dept->id);
                                    $quantity = $order ? $order->quantity : 0;
                                    $totalQuantity += $quantity;
                                    @endphp
                                    <td class="relative p-0 h-full">
                                        <input type="text" value="{{ $quantity > 0 ? ($quantity + 0) : '' }}"
                                            @focus="$el.dataset.oldValue = $el.value"
                                            @change="handleQuantityChange($event, '{{ $product->id }}', '{{ $dept->id }}')"
                                            class="cell-input {{ $quantity > 0 ? 'text-slate-900 font-semibold' : 'text-gray-400' }}"
                                            placeholder="">
                                        <div class="saving-indicator hidden">
                                            <span
                                                class="material-symbols-outlined text-[14px] text-emerald-500">check_circle</span>
                                        </div>
                                    </td>
                                    @endforeach
                                    <td class="text-right font-semibold" style="background: #fff3cd;">
                                        {{ number_format($totalQuantity, 0, ',', '.') }}
                                    </td>
                                </tr>
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
                                    $deptQty += $p->monthlyOrders->where('month',
                                    $selectedMonth)->where('department_id',
                                    $dept->id)->sum('quantity');
                                    }
                                    }
                                    }
                                    $overallQty += $deptQty;
                                    @endphp
                                    <td class="text-right">{{ $deptQty > 0 ? number_format($deptQty, 0, ',', '.') : ''
                                        }}
                                    </td>
                                    @endforeach
                                    <td class="text-right">{{ number_format($overallQty, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div><!-- end table scroll container -->

                    <!-- Modal Nhập Lý Do -->
                    <div x-show="showReasonModal" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 hidden" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
                        style="display: none;">
                        <div @click.away="cancelEdit()"
                            class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all p-6"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                            <!-- Header Modal -->
                            <div class="flex items-start mb-5">
                                <div
                                    class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:h-10 sm:w-10">
                                    <span class="material-symbols-outlined text-blue-600">verified_user</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <h3
                                        class="text-lg leading-6 font-bold text-gray-900 flex justify-between items-center">
                                        Xác Nhận Cập Nhật Lại Số Lượng
                                        <button @click="cancelEdit()" class="text-gray-400 hover:text-gray-500">
                                            <span class="material-symbols-outlined text-xl">close</span>
                                        </button>
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">Quý nhân viên vui lòng xác thực lý do hiệu
                                        chỉnh dữ liệu để đảm bảo tính minh bạch và an toàn.</p>
                                </div>
                            </div>

                            <hr class="border-gray-200 mb-5">

                            <!-- Chọn lý do nhanh -->
                            <div class="mb-5" x-show="!isSettingsMode">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="block text-xs font-bold text-gray-700 tracking-wider">QUÝ NHÂN VIÊN
                                        HÃY CHỌN LÝ DO:</label>
                                    <button @click="isSettingsMode = true"
                                        class="text-xs text-blue-600 flex items-center hover:text-blue-800 transition-colors">
                                        <span class="material-symbols-outlined text-[14px] mr-1">settings</span> THIẾT
                                        LẬP
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="(reason, index) in predefinedReasons" :key="index">
                                        <label
                                            class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors"
                                            :class="{ 'border-blue-500 bg-blue-50 ring-1 ring-blue-500': selectedReasonOption === reason.title }">
                                            <div class="flex items-center h-5">
                                                <input type="radio" x-model="selectedReasonOption" :value="reason.title"
                                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <span class="block text-sm font-medium text-gray-900"
                                                    x-text="reason.title"></span>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- Chế độ Thiết Lập (Settings Mode) -->
                            <div class="mb-5" x-show="isSettingsMode" style="display: none;">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="block text-xs font-bold text-gray-700 tracking-wider">THIẾT LẬP LÝ DO
                                        CHỈNH SỬA:</label>
                                </div>

                                <div class="max-h-60 overflow-y-auto pr-2 mb-3 space-y-2">
                                    <template x-for="(reason, index) in predefinedReasons" :key="index">
                                        <div
                                            class="flex gap-2 items-center bg-gray-50 p-2 rounded-lg border border-gray-200">
                                            <div class="flex-1">
                                                <input type="text" x-model="reason.title"
                                                    class="w-full border-gray-300 rounded text-sm p-2"
                                                    placeholder="Tên lý do (VD: Hàng hết hạn)">
                                            </div>
                                            <button @click="removeReason(index)"
                                                :disabled="reason.title === 'Lý do khác'"
                                                class="text-red-500 hover:text-red-700 p-1 disabled:opacity-30 disabled:cursor-not-allowed"
                                                title="Xóa">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex justify-between items-center mt-4">
                                    <button @click="addReason()"
                                        class="text-xs text-blue-600 flex items-center hover:text-blue-800 transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[16px] mr-1">add_circle</span> THÊM
                                        LÝ DO NỮA
                                    </button>
                                    <button @click="saveSettings()"
                                        class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-md hover:bg-indigo-200 transition-colors font-medium">
                                        LƯU THIẾT LẬP
                                    </button>
                                </div>
                            </div>

                            <!-- Nhập lý do chi tiết (Chỉ hiện khi chọn "Lý do khác") -->
                            <div class="mb-6" x-show="selectedReasonOption === 'Lý do khác' && !isSettingsMode"
                                style="display: none;">
                                <label class="block text-xs font-bold text-gray-700 tracking-wider mb-2">BẠN HÃY NHẬP LÝ
                                    DO CHI TIẾT:</label>
                                <div class="relative">
                                    <input type="text" x-model="detailedReason" x-ref="detailedReasonInput"
                                        class="w-full border-gray-200 bg-gray-50 rounded-lg pl-4 pr-10 py-3 text-sm focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors"
                                        placeholder="Nhập lý do thực tế..." @keydown.enter="confirmEdit()">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span
                                            class="text-[10px] text-gray-400 font-medium uppercase tracking-wider mr-1">MEDCORE
                                            SECURE</span>
                                        <span class="material-symbols-outlined text-gray-400 text-sm">lock</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Báo lỗi -->
                            <div x-show="errorMessage"
                                class="mb-4 text-sm text-red-600 bg-red-50 py-2 px-3 rounded-md border border-red-200 flex items-center">
                                <span class="material-symbols-outlined text-[16px] mr-1">error</span>
                                <span x-text="errorMessage"></span>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <button type="button" @click="cancelEdit()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-900 focus:outline-none transition-colors">
                                    Hủy Bỏ
                                </button>
                                <button type="button" @click="confirmEdit()"
                                    class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                    :class="{ 'opacity-70 cursor-not-allowed': isSaving }" :disabled="isSaving">
                                    <span x-show="!isSaving">Xác Nhận Cập Nhật</span>
                                    <span x-show="isSaving">Đang xử lý...</span>
                                    <span class="material-symbols-outlined text-[18px] ml-1">arrow_forward</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TỔNG HỢP Tab -->
                <div id="content-tong-hop"
                    class="tab-content hidden flex flex-col bg-white rounded-b-lg shadow-sm border border-gray-200">
                    <!-- Print Header -->
                    <div class="print-header p-6 text-[14px] leading-relaxed">
                        <div class="flex justify-between items-start mb-4">
                            <!-- Left Block -->
                            <div class="text-center w-1/2">
                                <div class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH</div>
                                <div class="font-bold uppercase">BỘ PHẬN HỖ TRỢ DỊCH VỤ</div>
                            </div>
                            <!-- Right Block -->
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
                            <div class="font-bold text-[18px] uppercase"> BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU
                                HAO
                                (BỆNH VIỆN) </div>
                            <div class="font-semibold mt-1"> Tháng {{ $selectedMonth }} </div>
                        </div>
                        <div class="mt-6 text-left">
                            <p>- Căn cứ vào tình hình hoạt động thực tế tại đơn vị;</p>
                            <p> - Căn cứ đề nghị các khoa/phòng tháng {{ $selectedMonth }} về thực tế nhu cầu sử dụng
                                văn
                                phòng phẩm vật tư tiêu hao hàng tháng trong phục vụ hoạt động chuyên môn của bệnh viện;
                            </p>
                            <p class="mt-2"> Nay Bộ phận hỗ trợ dịch vụ kính trình Ban Giám Đốc phê duyệt mua VPP-VTTH
                                tháng
                                {{ $selectedMonth }}.
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="font-bold"> Tổng số tiền: <span class="pdf-total-numeric"></span> đ </div>
                            <div class="italic"> Số tiền bằng chữ: <span class="pdf-total-text"></span> </div>
                        </div>
                    </div>

                    <div class="w-full">
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

                                        <!-- Quick Notes Manager Popover -->
                                        <div id="noteManager" x-show="showNoteManager"
                                            @click.away="showNoteManager = false" x-data="{ 
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
                                        }" class="absolute right-0 top-full mt-1 w-64 bg-white border shadow-xl rounded-lg z-50 p-3 text-left"
                                            style="display: none;">
                                            <h4 class="text-sm font-bold text-gray-700 mb-2">Quản lý ghi chú nhanh</h4>
                                            <div class="flex gap-1 mb-2">
                                                <input type="text" x-model="newNote" @keydown.enter="add()"
                                                    placeholder="Thêm ghi chú..."
                                                    class="flex-1 text-xs border rounded px-2 py-1">
                                                <button @click="add()"
                                                    class="bg-blue-600 text-white px-2 rounded hover:bg-blue-700">
                                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                                </button>
                                            </div>
                                            <ul class="max-h-48 overflow-y-auto text-xs space-y-1">
                                                <template x-for="(sug, index) in suggestions" :key="index">
                                                    <li
                                                        class="flex justify-between items-center group/item hover:bg-gray-50 p-1 rounded">
                                                        <span class="truncate" x-text="sug"></span>
                                                        <button @click="remove(index)"
                                                            class="text-gray-400 hover:text-red-600 opacity-0 group-hover/item:opacity-100">
                                                            <span
                                                                class="material-symbols-outlined text-[14px]">close</span>
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
                                @php
                                $catProducts = $products[$category->id]->filter(function($p) use ($selectedMonth) {
                                return $p->monthlyOrders->where('month', $selectedMonth)->sum('quantity') > 0;
                                });
                                @endphp

                                @if($catProducts->count() > 0)
                                <!-- Category Header -->
                                <tr class="category-header">
                                    <td colspan="7">
                                        <span class="sticky left-0 pl-1 inline-block">{{ mb_strtoupper($category->name,
                                            'UTF-8') }}</span>
                                    </td>
                                </tr>

                                <!-- Products -->
                                @foreach($products[$category->id] as $product)
                                @php
                                $stt++;
                                // Correctly filter orders for this month only
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
                                    $priceToUse = $product->price;
                                    foreach ($departments as $dept) {
                                    $order = $monthlyOrders->firstWhere('department_id', $dept->id);
                                    if ($order && $order->price > 0) {
                                    $priceToUse = $order->price;
                                    }
                                    $quantity = $order ? $order->quantity : 0;
                                    $totalQuantity += $quantity;
                                    }
                                    $totalAmount = $totalQuantity * $priceToUse;
                                    @endphp
                                    <td class="text-right font-bold">{{ number_format($totalQuantity, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">{{ number_format($priceToUse, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-red-600">
                                        {{ number_format($totalAmount, 0, ',', '.') }}
                                    </td>
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
                                                        <span
                                                            class="material-symbols-outlined text-[16px] text-blue-500 animate-spin">sync</span>
                                                    </template>
                                                    <template x-if="!saving">
                                                        <span
                                                            class="material-symbols-outlined text-[16px] text-gray-300 group-hover:text-blue-500 transition-colors cursor-pointer"
                                                            @click="dropdownOpen = !dropdownOpen">expand_more</span>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Suggestions Dropdown -->
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
                                    @endif
                                    @endforeach

                                    <!-- Category Total -->
                                <tr class="category-total">
                                    <td colspan="5" class="text-right">Cộng:</td>
                                    @php
                                    $catTotalAmount = 0;
                                    foreach($products[$category->id] as $p) {
                                    $pOrders = $p->monthlyOrders->where('month', $selectedMonth);
                                    $pQty = $pOrders->sum('quantity');
                                    $pPrice = $p->price;
                                    if ($pOrders->count() > 0 && $pOrders->first()->price > 0) {
                                    $pPrice = $pOrders->first()->price;
                                    }
                                    $catTotalAmount += $pQty * $pPrice;
                                    }
                                    @endphp
                                    <td class="text-right font-bold text-red-600">
                                        {{ number_format($catTotalAmount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endif
                                @endif
                                @endforeach

                                <!-- Total Quantity Row -->
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
                                    <td class="text-right font-extrabold text-blue-700">{{
                                        number_format($totalOverallQty,
                                        0, ',', '.') }}</td>
                                    <td colspan="2"></td>
                                </tr>

                                <!-- Grand Total -->
                                <tr class="grand-total">
                                    <td colspan="5" class="text-right text-lg">TỔNG CỘNG SỐ TIỀN:</td>
                                    <td class="text-right text-lg">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Signature Section -->
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

                <!-- PHIẾU XUẤT KHO Tab -->
                <div id="content-phieu-xuat-kho"
                    class="tab-content hidden bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden"
                    x-data="stockIssueApp()">

                    <!-- Controls -->
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

                    <!-- Report Content -->
                    <div class="p-8">
                        <!-- Table -->
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
                                    <!-- Category Header -->
                                    <tr class="bg-blue-50">
                                        <td colspan="7"
                                            class="text-left font-bold text-blue-800 italic border px-3 py-2"
                                            x-text="romanize(index + 1) + '. ' + cat.name.toUpperCase()"></td>
                                    </tr>
                                    <!-- Products -->
                                    <template x-for="(prod, idx) in cat.products" :key="prod.id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="text-center border px-3 py-2 text-gray-600" x-text="idx + 1">
                                            </td>
                                            <td class="font-medium border px-3 py-2 text-left" x-text="prod.name"></td>
                                            <td class="text-center border px-3 py-2" x-text="prod.unit"></td>
                                            <td class="text-center font-bold text-red-600 border px-3 py-2"
                                                x-text="formatNumber(prod.quantity, 0)"></td>
                                            <td class="text-right text-green-600 border px-3 py-2"
                                                x-text="formatNumber(prod.price, 0)"></td>
                                            <td class="text-right font-bold text-green-700 border px-3 py-2"
                                                x-text="formatNumber(prod.total, 0)"></td>
                                            <td class="border px-1 py-1 w-[150px]">
                                                <div class="relative group">
                                                    <input type="text" x-model="prod.note"
                                                        @change="savePrivateNote(prod, prod.note)"
                                                        @keydown.enter="$event.target.blur()"
                                                        class="w-full border-transparent hover:border-blue-300 rounded text-xs px-2 py-1 transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-transparent hover:bg-white appearance-none h-8"
                                                        placeholder="...">
                                                    <div
                                                        class="absolute right-1 top-1/2 -translate-y-1/2 flex items-center">
                                                        <span
                                                            class="material-symbols-outlined text-[14px] text-blue-500 animate-spin"
                                                            x-show="prod.saving">sync</span>
                                                        <span
                                                            class="material-symbols-outlined text-[14px] text-green-500"
                                                            x-show="prod.saved && !prod.saving">check</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <!-- Category Subtotal -->
                                    <tr class="bg-blue-50">
                                        <td colspan="5" class="text-right font-bold text-gray-700 border px-3 py-2">CỘNG
                                            NHÓM (<span x-text="romanize(index + 1)"></span>):</td>
                                        <td class="text-right font-bold text-blue-700 border px-3 py-2"
                                            x-text="formatNumber(cat.total, 0)"></td>
                                        <td class="border bg-white"></td>
                                    </tr>
                                </tbody>
                            </template>

                            <!-- Grand Total -->
                            <tfoot>
                                <tr class="bg-gray-100">
                                    <td colspan="5"
                                        class="text-right font-bold text-red-600 uppercase border px-3 py-3 text-lg">
                                        TỔNG
                                        CỘNG PHIẾU YÊU CẦU:</td>
                                    <td class="text-right font-bold text-red-600 border px-3 py-3 text-lg"
                                        x-text="formatNumber(grandTotal, 0)"></td>
                                    <td class="border w-[150px]"></td>
                                </tr>
                            </tfoot>
                        </table>


                    </div>

                </div>
            </div>
    </div>
    </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        function switchTab(tabName) {
            // Save to localStorage
            localStorage.setItem('active_consolidated_tab', tabName);

            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active state from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-600', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active state to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            if (activeTab) {
                activeTab.classList.remove('border-transparent', 'text-gray-500');
                activeTab.classList.add('border-blue-600', 'text-blue-600');
            }
        }

        // Initialize tab on load
        document.addEventListener('DOMContentLoaded', function () {
            // Function to update thead height for sticky category headers
            function updateTheadHeight() {
                const pageHeader = document.getElementById('pageHeader');
                const pageHeaderH = pageHeader ? pageHeader.offsetHeight : 0;

                const tables = document.querySelectorAll('.excel-table');
                tables.forEach(table => {
                    // thead should stick just below the sticky pageHeader
                    const theadTop = pageHeaderH;
                    table.style.setProperty('--table-top-offset', theadTop + 'px');

                    const thead = table.querySelector('thead');
                    if (thead) {
                        const theadHeight = thead.offsetHeight;
                        table.style.setProperty('--table-category-offset', (theadTop + theadHeight) + 'px');
                    }
                });
            }

            // Update on load and resize
            updateTheadHeight();
            window.addEventListener('resize', updateTheadHeight);

            // Also update after some time as layout might shift
            setTimeout(updateTheadHeight, 500);

            const savedTab = localStorage.getItem('active_consolidated_tab');
            if (savedTab) {
                switchTab(savedTab);
            } else {
                // Default to 'bang-tong' if nothing saved
                switchTab('bang-tong');
            }

            // Need to re-calculate after tab switch
            window.addEventListener('click', (e) => {
                if (e.target.classList.contains('tab-button')) {
                    setTimeout(updateTheadHeight, 100);
                }
            });
        });

        function exportToExcel() {
            // Get the TỔNG HỢP table
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

            // Show loading state
            const btn = event.currentTarget;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span> Đang chuẩn bị PDF...';
            btn.disabled = true;

            try {
                // Fetch the printable HTML content
                const response = await fetch(printUrl);
                const html = await response.text();

                // Create a temporary container to hold the HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;

                // Extract only the part we want to print (the pages)
                // In our consolidated-print.blade.php, we have .page elements
                // Extract CSS Styles (crucial for colors)
                const styles = tempDiv.querySelectorAll('style');
                const container = document.createElement('div');

                // Add styles to container
                styles.forEach(style => {
                    container.appendChild(style.cloneNode(true));
                });

                // Extract only the part we want to print (the pages)
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
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        letterRendering: true
                    },
                    jsPDF: { unit: 'in', format: 'a4', orientation: activeTab === 'bang-tong' ? 'landscape' : 'portrait' },
                    pagebreak: { mode: ['css', 'legacy'] }
                };

                // Generate PDF
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

            // Show a simple loading indicator if needed, but the iframe itself is hidden
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
                    // Fallback to new tab if iframe fails
                    window.open(printUrl, '_blank');
                } finally {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            };

            printFrame.src = printUrl;
        }

        function filterBangTong() {
            const selectProduct = document.getElementById("searchBangTong");
            if (!selectProduct) return;
            const filterProduct = selectProduct.value.toUpperCase();

            const selectMode = document.getElementById("viewModeBangTong");
            const viewMode = selectMode ? selectMode.value : "requested";

            const table = document.getElementById("tableBangTong");
            if (!table) return;
            const tr = table.getElementsByTagName("tr");

            let stt = 0;
            let currentCategoryRow = null;
            let hasVisibleProductsInCategory = false;

            for (let i = 0; i < tr.length; i++) {
                const row = tr[i];
                if (row.parentElement.tagName === 'THEAD' || row.parentElement.tagName === 'TFOOT') continue;

                if (row.classList.contains("category-header")) {
                    if (currentCategoryRow && !hasVisibleProductsInCategory) {
                        currentCategoryRow.style.display = "none";
                    }
                    currentCategoryRow = row;
                    hasVisibleProductsInCategory = false;
                    row.style.display = "";
                } else if (row.classList.contains("product-row")) {
                    const nameTd = row.getElementsByTagName("td")[1];
                    const txtValue = nameTd ? (nameTd.textContent || nameTd.innerText) : "";
                    const hasOrders = row.dataset.hasOrders === 'true';

                    let matchProduct = (filterProduct === "" || txtValue.toUpperCase() === filterProduct);
                    let matchMode = (viewMode === 'all' || hasOrders);

                    if (matchProduct && matchMode) {
                        row.style.display = "";
                        hasVisibleProductsInCategory = true;

                        // Cập nhật STT
                        stt++;
                        const sttTd = row.querySelector(".row-stt");
                        if (sttTd) sttTd.innerText = stt;
                    } else {
                        row.style.display = "none";
                    }
                }
            }

            if (currentCategoryRow && !hasVisibleProductsInCategory) {
                currentCategoryRow.style.display = "none";
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(filterBangTong, 200);
        });
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
                    console.log('SmartNote init for product:', this.productId, 'Initial note:', this.note);

                    // Split the note to only show the shared part (Part 1)
                    if (this.note && this.note.includes('|||')) {
                        this.note = this.note.split('|||')[0].trim();
                    }

                    this.loadSuggestions();
                    this.$watch('note', (value, oldValue) => {
                        if (value !== oldValue) {
                            console.log('Note changed for', this.productId, ':', value);
                            this.triggerAutoSave();
                        }
                    });

                    // Sync with other instances
                    window.addEventListener('suggestions-updated', () => this.loadSuggestions());
                },

                loadSuggestions() {
                    const saved = localStorage.getItem('hospital_quick_notes');
                    this.suggestions = saved ? JSON.parse(saved) : ['Cần gấp', 'Hàng thường xuyên', 'Mua theo yêu cầu', 'Đã đặt hàng', 'Chờ duyệt', 'Ưu tiên', 'Không gấp'];
                },

                triggerAutoSave() {
                    if (this.saveTimeout) clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => {
                        this.saveToServer();
                    }, 800);
                },

                async saveToServer() {
                    console.log('Auto-saving note for product', this.productId, ':', this.note);
                    this.saving = true;
                    this.bgColor = '#f3f4f6'; // gray-100

                    try {
                        const response = await fetch('{{ route("admin.consolidated.update_note") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                product_id: this.productId,
                                month: this.month,
                                note: this.note
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.bgColor = '#d1fae5'; // green-100
                            setTimeout(() => { this.bgColor = 'transparent'; }, 800);
                        } else {
                            this.bgColor = '#fee2e2'; // red-100
                        }
                    } catch (e) {
                        console.error('Auto-save error:', e);
                        this.bgColor = '#fee2e2'; // red-100
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

        // Note manager toggle is now handled by Alpine.js x-show and @click.away
    </script>

    @php
    $jsonDepartments = $departments->map(function ($dept) {
    return [
    'id' => $dept->id,
    'name' => $dept->name
    ];
    });

    $deptData = [];
    foreach ($departments as $dept) {
    $deptCats = [];
    foreach ($categories as $cat) {
    $catProducts = [];
    if (isset($products[$cat->id])) {
    foreach ($products[$cat->id] as $product) {
    // AGGREGATE orders for CURRENT MONTH AND THIS DEPARTMENT (matching aggregated import)
    $matchingOrders = $product->monthlyOrders
    ->where('department_id', $dept->id)
    ->where('month', $selectedMonth);

    $totalQty = $matchingOrders->sum('quantity');

    if ($totalQty > 0) {
    $priceToUse = $product->price;
    if ($matchingOrders->count() > 0 && $matchingOrders->first()->price > 0) {
    $priceToUse = $matchingOrders->first()->price;
    }
    $catProducts[] = [
    'id' => $product->id,
    'name' => $product->name,
    'unit' => $product->unit,
    'quantity' => $totalQty,
    'price' => $priceToUse,
    'total' => $totalQty * $priceToUse,
    'note' => $matchingOrders->map(function($o) {
    $notes = [];
    if ($o->notes) $notes[] = $o->notes;

    // Extract only the part after ||| (Part 2) for PHIẾU XUẤT KHO
    if ($o->admin_notes) {
    $parts = explode('|||', $o->admin_notes);
    $privatePart = isset($parts[1]) ? trim($parts[1]) : '';
    if ($privatePart !== '') {
    $notes[] = $privatePart;
    }
    }
    return implode(' - ', $notes);
    })->filter()->implode('; ')
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
                showReasonModal: false,
                isSettingsMode: false,
                isSaving: false,
                errorMessage: '',
                pendingEdit: null,
                selectedReasonOption: '',
                detailedReason: '',
                predefinedReasons: [
                    { title: 'Lý do khác' }
                ],

                init() {
                    let savedReasons = localStorage.getItem('hospital_purchase_reasons');
                    if (savedReasons) {
                        try {
                            this.predefinedReasons = JSON.parse(savedReasons);
                        } catch (e) {
                            console.error("Lỗi parse JSON lý do:", e);
                        }
                    } else {
                        // Default reasons if empty
                        this.predefinedReasons = [
                            { title: 'Sai số lượng thực tế trong kho' },
                            { title: 'Cập nhật theo chỉ định Bác sĩ' },
                            { title: 'Hàng hóa bị lỗi hoặc hết hạn' },
                            { title: 'Cập nhật bảo hiểm y tế' },
                            { title: 'Điều chỉnh thông tin hành chính' },
                            { title: 'Lý do khác' }
                        ];
                    }

                    // Focus detailed reason when selecting "Lý do khác"
                    this.$watch('selectedReasonOption', value => {
                        if (value === 'Lý do khác') {
                            this.$nextTick(() => {
                                if (this.$refs.detailedReasonInput) this.$refs.detailedReasonInput.focus();
                            });
                        }
                    });
                },

                addReason() {
                    // Thêm vào trước "Lý do khác" nếu có
                    let otherIndex = this.predefinedReasons.findIndex(r => r.title === 'Lý do khác');
                    let newReason = { title: '' };
                    if (otherIndex >= 0) {
                        this.predefinedReasons.splice(otherIndex, 0, newReason);
                    } else {
                        this.predefinedReasons.push(newReason);
                    }
                },

                removeReason(index) {
                    if (this.predefinedReasons[index].title !== 'Lý do khác') {
                        this.predefinedReasons.splice(index, 1);
                    }
                },

                saveSettings() {
                    // Dọn dẹp danh sách (xóa các mục trống)
                    this.predefinedReasons = this.predefinedReasons.filter(r => r.title.trim() !== '' || r.title === 'Lý do khác');

                    // Đảm bảo luôn có "Lý do khác" ở cuối
                    if (!this.predefinedReasons.some(r => r.title === 'Lý do khác')) {
                        this.predefinedReasons.push({ title: 'Lý do khác' });
                    }

                    localStorage.setItem('hospital_purchase_reasons', JSON.stringify(this.predefinedReasons));
                    this.isSettingsMode = false;
                },

                saveQuantity(event, productId, deptId) {
                    // Logic đã được đóng gói trong handleQuantityChange
                },

                handleQuantityChange(event, productId, deptId) {
                    let input = event.target;
                    let value = input.value;
                    let cell = input.closest('td');
                    let indicator = cell.querySelector('.saving-indicator');

                    // Hiển thị modal thay vì window.prompt
                    this.pendingEdit = {
                        input: input,
                        value: value,
                        cell: cell,
                        indicator: indicator,
                        productId: productId,
                        deptId: deptId
                    };

                    this.selectedReasonOption = '';
                    this.detailedReason = '';
                    this.errorMessage = '';
                    this.showReasonModal = true;
                },

                cancelEdit() {
                    if (this.pendingEdit && this.pendingEdit.input) {
                        this.pendingEdit.input.value = this.pendingEdit.input.dataset.oldValue;
                    }
                    this.isSettingsMode = false;
                    this.showReasonModal = false;
                    this.pendingEdit = null;
                    this.errorMessage = '';
                },

                confirmEdit() {
                    if (this.isSettingsMode) return; // Không cho xác nhận nếu đang ở chế độ cài đặt
                    if (!this.pendingEdit) return;

                    let reasonParts = [];
                    if (this.selectedReasonOption) reasonParts.push(this.selectedReasonOption);
                    if (this.detailedReason && this.detailedReason.trim()) reasonParts.push(this.detailedReason.trim());

                    let finalReason = reasonParts.join(' - ');

                    // Nếu không có lý do nào được nhập/chọn, có bắt buộc không? 
                    // Yêu cầu cũ: Để trống nếu không muốn ghi lý do
                    // Tạm thời cho phép trống

                    let { input, value, indicator, productId, deptId } = this.pendingEdit;

                    this.isSaving = true;
                    this.errorMessage = '';
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
                            quantity: value,
                            reason: finalReason
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (value > 0) {
                                    input.classList.remove('text-gray-400', 'bg-red-50');
                                    input.classList.add('text-slate-900', 'font-semibold');

                                    // Cập nhật trạng thái dòng để không bị ẩn nếu đang ở chế độ "có yêu cầu"
                                    let row = input.closest('tr');
                                    if (row) {
                                        row.dataset.hasOrders = 'true';
                                        row.classList.remove('no-orders', 'opacity-60');
                                        row.classList.add('has-orders');
                                    }
                                } else {
                                    input.classList.remove('text-slate-900', 'font-semibold', 'bg-red-50');
                                    input.classList.add('text-gray-400');
                                }

                                // Phát sự kiện để cập nhật tab Phiếu Xuất Kho
                                window.dispatchEvent(new CustomEvent('quantity-updated', {
                                    detail: {
                                        productId: productId,
                                        deptId: deptId,
                                        quantity: value,
                                        reason: finalReason,
                                        price: data.price || 0
                                    }
                                }));

                                indicator.classList.remove('hidden');
                                setTimeout(() => {
                                    indicator.classList.add('hidden');
                                    input.classList.remove('bg-indigo-50');
                                }, 1000);

                                this.showReasonModal = false;
                                this.pendingEdit = null;
                            } else {
                                this.errorMessage = data.message || 'Lỗi lưu dữ liệu. Vui lòng thử lại.';
                                input.classList.remove('bg-indigo-50');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.errorMessage = 'Mất kết nối máy chủ. Vui lòng thử lại.';
                            input.classList.add('bg-red-50');
                            input.classList.remove('bg-indigo-50');
                        })
                        .finally(() => {
                            this.isSaving = false;
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

                    // Lắng nghe sự kiện cập nhật số lượng từ Bảng Tổng
                    window.addEventListener('quantity-updated', (e) => {
                        const { productId, deptId, quantity, reason, price } = e.detail;
                        this.syncQuantities(productId, deptId, quantity, reason, price);
                    });

                    // Initial update if dept is selected
                    if (this.selectedDeptId) {
                        this.updateView(this.selectedDeptId);
                    }
                },

                syncQuantities(productId, deptId, quantity, reason, price) {
                    if (!this.allDeptData[deptId]) return;

                    let found = false;
                    for (let cat of this.allDeptData[deptId]) {
                        for (let prod of cat.products) {
                            if (prod.id == productId) {
                                prod.quantity = parseFloat(quantity) || 0;
                                prod.total = prod.quantity * (price || prod.price);
                                prod.note = reason; // Cập nhật lý do vào ghi chú
                                found = true;
                                break;
                            }
                        }
                        if (found) {
                            // Cập nhật lại tổng của Category
                            cat.total = cat.products.reduce((sum, p) => sum + p.total, 0);
                            break;
                        }
                    }

                    // Nếu đang xem đúng khoa đó thì cập nhật view ngay
                    if (this.selectedDeptId == deptId) {
                        this.updateView(deptId);
                    }
                },

                updateView(deptId) {
                    const dept = this.departments.find(d => d.id == deptId);
                    this.selectedDeptName = dept ? dept.name : '';
                    this.currentDeptData = this.allDeptData[deptId] || [];

                    this.grandTotal = 0;
                    this.currentDeptData.forEach(cat => {
                        this.grandTotal += cat.total;
                    });
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
                        roman = "",
                        i = 3;
                    while (i--)
                        roman = (key[+digits.pop() + (i * 10)] || "") + roman;
                    return Array(+digits.join("") + 1).join("M") + roman;
                },

                editPrivateNote(prod) {
                    // This method is no longer needed for inline editing
                },

                savePrivateNote(prod, newNote) {
                    prod.saving = true;
                    prod.saved = false;

                    fetch('{{ route("admin.consolidated.update_private_note") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: prod.id,
                            department_id: this.selectedDeptId,
                            month: '{{ $selectedMonth }}',
                            note: newNote
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                prod.note = data.display_note || newNote;

                                // For safety, force sync with allDeptData as well
                                if (this.allDeptData && this.allDeptData[this.selectedDeptId]) {
                                    let cats = this.allDeptData[this.selectedDeptId];
                                    for (let cat of cats) {
                                        let p = cat.products.find(item => item.id == prod.id);
                                        if (p) {
                                            p.note = prod.note;
                                            break;
                                        }
                                    }
                                }

                                prod.saved = true;
                                setTimeout(() => { prod.saved = false; }, 2000);
                            } else {
                                alert(data.message || 'Lỗi lưu ghi chú.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Mất kết nối máy chủ. Vui lòng thử lại.');
                        })
                        .finally(() => {
                            prod.saving = false;
                            this.isSaving = false;
                        });
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

                changeYear(dir) {
                    this.pickerYear = parseInt(this.pickerYear) + dir;
                },

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
                    if (this.showPicker) {
                        finalMonth = this.pickerMonth + '/' + this.pickerYear;
                    }
                    let url = "{{ route('admin.consolidated') }}?month=" + encodeURIComponent(finalMonth);
                    if (this.category) {
                        url += "&category=" + encodeURIComponent(this.category);
                    }
                    window.location.href = url;
                }
            }
        }
    </script>
</body>

</html>