<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tổng hợp yêu cầu - Tháng {{ $selectedMonth }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .excel-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
        }

        .excel-table th {
            background: #f3f4f6;
            font-weight: 600;
            text-align: center;
        }

        .category-header {
            background: #3b82f6 !important;
            color: white;
            font-weight: bold;
            text-align: left;
        }

        .category-total {
            background: #fef3c7;
            font-weight: bold;
        }

        .grand-total {
            background: #fbbf24;
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .print-header {
                display: block !important;
            }

            .excel-table {
                font-size: 11px;
            }

            .excel-table th,
            .excel-table td {
                padding: 4px 6px;
            }

            @page {
                margin: 1.5cm;
            }
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
            .signature-section {
                display: flex !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50" x-data="{ showNoteManager: false }">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col no-print">
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-tmmc.png') }}" alt="Logo" class="h-12 w-auto">
                    <div class="flex flex-col">
                        <h2 class="text-slate-900 text-xl font-black leading-none tracking-tighter">TÂM TRÍ</h2>
                        <span class="text-[10px] font-bold text-slate-800 uppercase tracking-widest mt-1">CAO
                            LÃNH</span>
                        <span class="text-[9px] font-medium text-slate-500 leading-none mt-0.5 uppercase">Hệ Thống Vật
                            Tư</span>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.consolidated') }}"
                    class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">summarize</span>
                    <span>Tổng hợp yêu cầu</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            AD
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">Admin</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit"
                            class="w-full text-xs text-gray-500 hover:text-blue-600 text-left px-2 py-1">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center no-print">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Tổng hợp yêu cầu VPP</h1>
                    <p class="text-sm text-gray-500">Tháng {{ $selectedMonth }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Category Filter -->
                    <form method="GET" action="{{ route('admin.consolidated') }}" class="flex items-center gap-2">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <select name="category" onchange="this.form.submit()"
                            class="border-gray-300 rounded-lg text-sm px-4 py-2">
                            <option value="">Tất cả danh mục</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <!-- Month Filter -->
                    <form method="GET" action="{{ route('admin.consolidated') }}">
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <select name="month" onchange="this.form.submit()"
                            class="border-gray-300 rounded-lg text-sm px-4 py-2">
                            @for($i = 0; $i < 12; $i++)
                                @php
                                    $date = now()->subMonths($i);
                                    $monthValue = $date->format('m/Y');
                                @endphp
                                <option value="{{ $monthValue }}" {{ $selectedMonth == $monthValue ? 'selected' : '' }}>
                                    Tháng {{ $date->format('m/Y') }}
                                </option>
                            @endfor
                        </select>
                    </form>
                    <a href="{{ route('admin.consolidated.print', ['month' => $selectedMonth]) }}" target="_blank"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">print</span>
                        In
                    </a>
                    <a href="{{ route('admin.consolidated.export', ['month' => $selectedMonth]) }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">table_chart</span>
                        Excel
                    </a>
                    <button onclick="exportToPDF()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                        PDF
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8">
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
                </div>

                <!-- BẢNG TỔNG Tab -->
                <div id="content-bang-tong"
                    class="tab-content bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">STT</th>
                                <th style="width: 250px;">TÊN HÀNG</th>
                                <th style="width: 80px;">ĐVT</th>
                                @foreach($departments as $dept)
                                    <th style="width: 100px; background: #d4edda;">{{ strtoupper($dept->name) }}</th>
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
                                        <td colspan="{{ 4 + $departments->count() }}">{{ strtoupper($category->name) }}</td>
                                    </tr>

                                    <!-- Products -->
                                    @foreach($products[$category->id] as $product)
                                        @php
                                            $stt++;
                                            $hasOrders = $product->monthlyOrders->count() > 0;
                                            $totalQuantity = 0;
                                        @endphp
                                        @if($hasOrders)
                                            <tr>
                                                <td class="text-center text-gray-600">{{ $stt }}</td>
                                                <td class="font-medium">{{ $product->name }}</td>
                                                <td class="text-center">{{ $product->unit }}</td>
                                                @foreach($departments as $dept)
                                                    @php
                                                        $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                                                        $quantity = $order ? $order->quantity : 0;
                                                        $totalQuantity += $quantity;
                                                    @endphp
                                                    <td class="text-right {{ $quantity > 0 ? 'font-semibold' : 'text-gray-400' }}">
                                                        {{ $quantity > 0 ? number_format($quantity, 0, ',', '.') : '' }}
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
                    </table>
                </div>

                <!-- TỔNG HỢP Tab -->
                <div id="content-tong-hop"
                    class="tab-content hidden bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Print Header -->
                    <div class="print-header p-6 text-[14px] leading-relaxed">
                        <div class="flex justify-between mb-4">
                            <div class="text-left">
                                <div class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA</div>
                                <div class="font-bold uppercase">TÂM TRÍ CAO LÃNH</div>
                                <div>Bộ phận hỗ trợ dịch vụ</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold uppercase">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                                <div class="font-semibold">Độc lập - Tự do - Hạnh phúc</div>
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
                                        <span>GHI CHÚ</span>
                                        <button @click.stop="showNoteManager = !showNoteManager"
                                            class="text-gray-400 hover:text-blue-600 p-1 rounded-full"
                                            title="Quản lý ghi chú nhanh">
                                            <span class="material-symbols-outlined text-[16px]">settings</span>
                                        </button>
                                    </div>

                                    <!-- Quick Notes Manager Popover -->
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
                                    <!-- Category Header -->
                                    <tr class="category-header">
                                        <td colspan="7">{{ strtoupper($category->name) }}</td>
                                    </tr>

                                    <!-- Products -->
                                    @foreach($products[$category->id] as $product)
                                        @php
                                            $stt++;
                                            $hasOrders = $product->monthlyOrders->count() > 0;
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
                                                        $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                                                        $quantity = $order ? $order->quantity : 0;
                                                        $totalQuantity += $quantity;
                                                    }
                                                    $totalAmount = $totalQuantity * $product->price;
                                                @endphp
                                                <td class="text-right font-bold">{{ number_format($totalQuantity, 1, ',', '.') }}
                                                </td>
                                                <td class="text-right">{{ number_format($product->price, 0, ',', '.') }}</td>
                                                <td class="text-right font-bold text-red-600">
                                                    {{ number_format($totalAmount, 0, ',', '.') }}
                                                </td>
                                                <td class="px-2 py-1 pdf-hide"
                                                    x-data="smartNote('{{ $product->id }}', '{{ $selectedMonth }}', {{ \Illuminate\Support\Js::from($product->monthlyOrders->first()->notes ?? '') }})">
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
                                        <td class="text-right font-bold text-red-600">
                                            {{ number_format($products[$category->id]->sum(fn($p) => $p->monthlyOrders->where('month', $selectedMonth)->sum('quantity') * $p->price), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            <!-- Grand Total -->
                            <tr class="grand-total">
                                <td colspan="5" class="text-right text-lg">TỔNG CỘNG:</td>
                                <td class="text-right text-lg">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

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
            const savedTab = localStorage.getItem('active_consolidated_tab');
            if (savedTab) {
                switchTab(savedTab);
            } else {
                // Default to 'bang-tong' if nothing saved
                switchTab('bang-tong');
            }
        });

        function exportToExcel() {
            // Get the TỔNG HỢP table
            const table = document.querySelector('#content-tong-hop .excel-table');
            const wb = XLSX.utils.table_to_book(table, { sheet: "Tổng hợp" });
            XLSX.writeFile(wb, 'Tong_hop_VPP_{{ $selectedMonth }}.xlsx');
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

        function exportToPDF() {
            const container = document.querySelector('#content-tong-hop');
            const element = container.cloneNode(true);
            element.classList.remove('hidden');

            // Apply PDF Container Styling (Centered A4 Landscape)
            element.style.width = '1100px';
            element.style.margin = '0 auto';
            element.style.backgroundColor = 'white';
            element.style.padding = '40px';
            element.style.fontFamily = "'Times New Roman', serif";

            // Set Date Fields
            const now = new Date();
            element.querySelectorAll('.print-date-day').forEach(el => el.innerText = now.getDate().toString().padStart(2, '0'));
            element.querySelectorAll('.print-date-month').forEach(el => el.innerText = (now.getMonth() + 1).toString().padStart(2, '0'));
            element.querySelectorAll('.print-date-year').forEach(el => el.innerText = now.getFullYear());

            // Set Totals
            const grandTotalValue = {{ (float) $grandTotal }};
            element.querySelectorAll('.pdf-total-numeric').forEach(el => el.innerText = new Intl.NumberFormat('vi-VN').format(grandTotalValue));
            element.querySelectorAll('.pdf-total-text').forEach(el => el.innerText = docSoThanhChu(grandTotalValue));

            // Setup Header and Sections
            const h = element.querySelector('.print-header');
            if (h) {
                h.classList.remove('hidden');
                h.style.display = 'block';
                h.style.marginBottom = '20px';
            }

            const s = element.querySelector('.signature-section');
            if (s) {
                s.classList.remove('hidden');
                s.classList.add('flex');
                s.style.display = 'flex';
                s.style.justifyContent = 'space-between';
                s.style.marginTop = '40px';
                s.style.paddingBottom = '30px'; // Ensure space before edge
            }

            // Remove unwanted columns/elements
            element.querySelectorAll('.pdf-hide, button, .material-symbols-outlined, #noteManager, [x-show="dropdownOpen"]').forEach(el => el.remove());

            // Explicit Border styling for PDF
            element.querySelectorAll('table').forEach(t => {
                t.style.borderCollapse = 'collapse';
                t.style.width = '100%';
                t.style.border = '1px solid black';
                t.style.marginBottom = '0';
            });

            element.querySelectorAll('th, td').forEach(cell => {
                cell.style.border = '1px solid black';
                cell.style.padding = '6px 4px'; // Slightly smaller padding
            });

            element.style.paddingBottom = '0px';

            const opt = {
                margin: [0.4, 0.4, 0.4, 0.4],
                filename: 'Bang_De_Nghi_Mua_VPP_{{ $selectedMonth }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    width: 1100,
                    scrollY: 0
                },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' },
                pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save();
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
                    console.log('SmartNote init for product:', this.productId, 'Initial note:', this.note);
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
</body>

</html>