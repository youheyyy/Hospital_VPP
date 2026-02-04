<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tổng hợp yêu cầu - Tháng {{ $selectedMonth }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .excel-table { border-collapse: collapse; width: 100%; font-size: 13px; }
        .excel-table th, .excel-table td { border: 1px solid #d1d5db; padding: 6px 8px; }
        .excel-table th { background: #f3f4f6; font-weight: 600; text-align: center; }
        .category-header { background: #3b82f6 !important; color: white; font-weight: bold; text-align: left; }
        .category-total { background: #fef3c7; font-weight: bold; }
        .grand-total { background: #fbbf24; font-weight: bold; }
        
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
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col no-print">
            <div class="p-6 border-b">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">local_hospital</span>
                    </div>
                    <h2 class="font-bold text-lg">VPP Hospital</h2>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.consolidated') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">summarize</span>
                    <span>Tổng hợp yêu cầu</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            AD
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">Admin</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-xs text-gray-500 hover:text-blue-600 text-left px-2 py-1">
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
                        <select name="category" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm px-4 py-2">
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
                        <select name="month" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm px-4 py-2">
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
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">print</span>
                        In
                    </button>
                    <button onclick="exportToExcel()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">table_chart</span>
                        Excel
                    </button>
                    <button onclick="exportToPDF()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                        PDF
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8">
                <!-- Tab Navigation -->
                <div class="bg-white rounded-t-lg shadow-sm border border-b-0 border-gray-200 flex">
                    <button onclick="switchTab('bang-tong')" id="tab-bang-tong" class="tab-button px-6 py-3 font-semibold text-sm border-b-2 border-blue-600 text-blue-600">
                        BẢNG TỔNG
                    </button>
                    <button onclick="switchTab('tong-hop')" id="tab-tong-hop" class="tab-button px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        TỔNG HỢP
                    </button>
                </div>

                <!-- BẢNG TỔNG Tab -->
                <div id="content-bang-tong" class="tab-content bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden">
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
                                                <td class="text-right font-semibold" style="background: #fff3cd;">{{ number_format($totalQuantity, 0, ',', '.') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- TỔNG HỢP Tab -->
                <div id="content-tong-hop" class="tab-content hidden bg-white rounded-b-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Print Header -->
                    <div class="print-header p-6">
                        <div class="text-sm mb-2">CÔNG TY CỔ PHẦN BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH</div>
                        <div class="text-xs mb-4">BỘ PHẬN HỖ TRỢ DỊCH VỤ</div>
                        <div class="text-xs italic mb-6">Đồng Tháp, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</div>
                        
                        <h2 class="text-lg font-bold mb-2">BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO (BỆNH VIỆN)</h2>
                        <div class="font-semibold mb-4">Tháng {{ $selectedMonth }}</div>
                        
                        <div class="text-sm text-left mb-4">
                            <p>Căn cứ vào tình hình hoạt động thực tế tại đơn vị;</p>
                            <p>Căn cứ đề nghị các khoa/phòng tháng {{ $selectedMonth }} về thực tế nhu cầu sử dụng văn phòng phẩm vật tư tiêu hao hàng tháng trong phục vụ hoạt động chuyên môn của bệnh viện;</p>
                            <p>Nay Bộ phận hỗ trợ dịch vụ kính trình Ban Giám Đốc phê duyệt mua VPP-VTTH tháng {{ $selectedMonth }}.</p>
                        </div>
                        
                        <div class="font-bold text-sm mb-2">Tổng số tiền: {{ number_format($grandTotal, 0, ',', '.') }} đ</div>
                        {{-- <div class="text-sm mb-4">Số tiền bằng chữ: <span class="italic">{{ ucfirst(convertNumberToWords($grandTotal)) }} đồng</span></div> --}}
                    </div>
                    
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">STT</th>
                                <th style="width: 250px;">TÊN VPP - VTTH</th>
                                <th style="width: 80px;">ĐVT</th>
                                <th style="width: 100px;">SỐ LƯỢNG</th>
                                <th style="width: 120px;">ĐƠN GIÁ</th>
                                <th style="width: 130px;">THÀNH TIỀN</th>
                                <th style="width: 150px;">GHI CHÚ</th>
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
                                                <td class="text-center text-gray-600">{{ $stt }}</td>
                                                <td class="font-medium">{{ $product->name }}</td>
                                                <td class="text-center">{{ $product->unit }}</td>
                                                @php
                                                    foreach($departments as $dept) {
                                                        $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                                                        $quantity = $order ? $order->quantity : 0;
                                                        $totalQuantity += $quantity;
                                                    }
                                                    $totalAmount = $totalQuantity * $product->price;
                                                @endphp
                                                <td class="text-right font-semibold">{{ number_format($totalQuantity, 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($product->price, 0, ',', '.') }}</td>
                                                <td class="text-right font-semibold text-red-600">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                                                <td></td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    <!-- Category Total -->
                                    <tr class="category-total">
                                        <td colspan="5" class="text-right">Cộng:</td>
                                        <td class="text-right">{{ number_format($categoryTotals[$category->id] ?? 0, 0, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                            @endforeach

                            <!-- Grand Total -->
                            <tr class="grand-total">
                                <td colspan="5" class="text-right text-lg">TỔNG CỘNG:</td>
                                <td class="text-right text-lg">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Signature Section -->
                    <div class="signature-section justify-around p-6">
                        <div class="text-center">
                            <div class="font-semibold mb-16">BP.HTDV</div>
                            <div class="font-semibold">Nguyễn Thị Thúy Trang</div>
                        </div>
                        <div class="text-center">
                            <div class="font-semibold mb-16">TRƯỞNG PHÒNG TCKT</div>
                            <div class="font-semibold">Nguyễn Thị Thúy Huỳnh</div>
                        </div>
                        <div class="text-center">
                            <div class="font-semibold mb-16">BAN GIÁM ĐỐC</div>
                            <div class="font-semibold">Huỳnh Thị Nguyệt</div>
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
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-600', 'text-blue-600');
        }
        
        function exportToExcel() {
            // Get the TỔNG HỢP table
            const table = document.querySelector('#content-tong-hop .excel-table');
            const wb = XLSX.utils.table_to_book(table, {sheet: "Tổng hợp"});
            XLSX.writeFile(wb, 'Tong_hop_VPP_{{ $selectedMonth }}.xlsx');
        }
        
        function exportToPDF() {
            window.print();
        }
    </script>
</body>

</html>
