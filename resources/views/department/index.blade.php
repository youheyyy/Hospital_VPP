<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $department->name }} - Yêu cầu tháng {{ $selectedMonth }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .excel-table { border-collapse: collapse; width: 100%; }
        .excel-table th, .excel-table td { border: 1px solid #d1d5db; padding: 8px 12px; }
        .excel-table th { background: #f3f4f6; font-weight: 600; text-align: center; }
        .category-header { background: #3b82f6 !important; color: white; font-weight: bold; text-align: left; }
        .product-row.hidden { display: none; }
        .total-row { background: #fef3c7; font-weight: bold; }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-6 border-b">
                <div class="flex flex-col items-center justify-center gap-3 w-full pt-2">
                    <img src="{{ asset('images/logo-tmmc.png') }}" class="h-20 w-auto object-contain" alt="Logo">
                    <div class="flex items-center justify-center gap-1.5 w-full">
                        <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.15em] whitespace-nowrap">Quản Lý Văn Phòng Phẩm</span>
                        <div class="h-[2px] w-4 bg-[#00a8e8] rounded-full"></div>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('department.index') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Yêu cầu VPP</span>
                </a>
                <a href="{{ route('department.history') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">history</span>
                    <span>Lịch sử yêu cầu</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ mb_strtoupper(mb_substr($department->name, 0, 2, 'UTF-8'), 'UTF-8') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ $department->name }}</p>
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
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">{{ $department->name }}</h1>
                    <p class="text-sm text-gray-500">Yêu cầu văn phòng phẩm tháng {{ $selectedMonth }}</p>
                    @if(!$canEdit)
                        <p class="text-xs text-red-600 mt-1">⚠️ Hạn chót chỉnh sửa yêu cầu tháng {{ $selectedMonth }} là ngày 25. Hiện tại chỉ có thể thêm sản phẩm mới.</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-100 rounded-lg px-4 py-2 border border-gray-200 shadow-sm font-semibold text-blue-600">
                        Tháng {{ $selectedMonth }}
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8">
                <form method="POST" action="{{ route('department.store') }}" id="requestForm">
                    @csrf
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">

                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->has('month'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <strong>⚠️ Lỗi:</strong> {{ $errors->first('month') }}
                        </div>
                    @endif

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <table class="excel-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Chọn</th>
                                    <th style="width: 50px;">STT</th>
                                    <th style="position: relative; min-width: 250px;">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-700 whitespace-nowrap">Tên hàng</span>
                                            <div class="relative flex-1">
                                                <input type="text" id="filterSearch" placeholder="Tìm kiếm & lọc..." 
                                                    onkeyup="filterProducts()" 
                                                    onclick="toggleFilter(event)"
                                                    class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 hover:bg-white transition-all">
                                                <span class="material-symbols-outlined absolute right-2 top-1.5 text-gray-400 text-sm pointer-events-none">search</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Filter Dropdown -->
                                        <div id="filterDropdown" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1000; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); width: 100%; min-width: 300px; max-height: 400px; overflow: hidden; margin-top: 4px;">
                                            <!-- Filter Options -->
                                            <div style="max-height: 300px; overflow-y: auto; padding: 12px;">
                                                <!-- Select All -->
                                                <label class="flex items-center px-3 py-2 hover:bg-blue-50 cursor-pointer rounded-lg transition-colors group">
                                                    <input type="checkbox" id="selectAllFilter" checked onchange="toggleAllFilters(this)" class="w-4 h-4 text-blue-600 rounded">
                                                    <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-blue-700">(Tất cả)</span>
                                                </label>
                                                
                                                <div class="my-2 border-t border-gray-100"></div>

                                                <!-- Product List -->
                                                <div id="filterProductList">
                                                    @foreach($products as $product)
                                                        <label class="filter-option flex items-center px-3 py-2 hover:bg-blue-50 cursor-pointer rounded-lg transition-colors group" data-product-name="{{ strtolower($product->name) }}">
                                                            <input type="checkbox" checked class="product-filter-checkbox w-4 h-4 text-blue-600 rounded" data-product-id="{{ $product->id }}" onchange="applyFilter()">
                                                            <span class="ml-3 text-sm text-gray-600 group-hover:text-blue-600">{{ $product->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <!-- Footer Buttons -->
                                            <div style="padding: 12px; border-top: 1px solid #f3f4f6; background: #fafafa; display: flex; justify-content: flex-end;">
                                                <button type="button" onclick="closeFilter()" class="px-6 py-1.5 text-xs font-bold text-gray-600 hover:text-blue-600 hover:bg-white border border-transparent hover:border-gray-200 rounded-md transition-all">Đóng</button>
                                            </div>
                                        </div>
                                    </th>
                                    <th style="width: 100px;">ĐVT</th>
                                    <th style="width: 120px;">Số lượng</th>
                                    <th style="width: 130px;">Đơn giá</th>
                                    <th style="width: 150px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $stt = 0; @endphp
                                @foreach($categories as $category)
                                    @php
                                        $categoryProducts = $products->where('category_id', $category->id);
                                        if($categoryProducts->isEmpty()) continue;
                                    @endphp
                                    
                                    <!-- Category Header -->
                                    <tr class="category-header">
                                        <td colspan="7">{{ mb_strtoupper($category->name, 'UTF-8') }}</td>
                                    </tr>

                                    <!-- Products -->
                                    @foreach($categoryProducts as $product)
                                        @php
                                            $stt++;
                                        @endphp
                                        <tr class="product-row" data-product-id="{{ $product->id }}">
                                            <td class="text-center">
                                                <input type="checkbox" 
                                                    class="product-checkbox w-4 h-4 text-blue-600 rounded {{ !$canEdit ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    data-product-id="{{ $product->id }}"
                                                    onchange="toggleProductRow(this)"
                                                    {{ isset($monthlyOrders[$product->id]) ? 'checked' : '' }}
                                                    {{ !$canEdit ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center text-sm text-gray-600">{{ $stt }}</td>
                                            <td class="text-sm font-medium">{{ $product->name }}</td>
                                            <td class="text-center text-sm">{{ $product->unit }}</td>
                                            <td>
                                                <input type="hidden" name="orders[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                                <input type="number" 
                                                    name="orders[{{ $product->id }}][quantity]"
                                                    class="quantity-input w-full border-gray-300 rounded text-sm px-2 py-1 text-right {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                    data-price="{{ $product->price }}"
                                                    data-product-id="{{ $product->id }}"
                                                     value="{{ isset($monthlyOrders[$product->id]) ? (int)$monthlyOrders[$product->id]->quantity : 0 }}"
                                                    min="0"
                                                    step="1"
                                                    oninput="calculateTotal(this)"
                                                    {{ !$canEdit ? 'readonly' : '' }}>
                                            </td>
                                            <td class="text-right text-sm price-cell">{{ number_format($product->price, 0, ',', '.') }}</td>
                                            <td class="text-right text-sm font-semibold total-cell">{{ number_format(($monthlyOrders[$product->id]->quantity ?? 0) * $product->price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach

                                <!-- Total Row -->
                                <tr class="total-row">
                                    <td colspan="6" class="text-right font-bold">TỔNG CỘNG:</td>
                                    <td class="text-right font-bold" id="grandTotal">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($canEdit)
                        <div class="mt-6 flex justify-end gap-4">
                            <button type="submit" class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg">
                                <span class="material-symbols-outlined text-sm inline-block align-middle">save</span>
                                Lưu yêu cầu
                            </button>
                        </div>
                    @else
                        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 text-red-700">
                            <span class="material-symbols-outlined">lock</span>
                            <p class="text-sm font-medium">Hệ thống đã khóa yêu cầu cho tháng {{ $selectedMonth }}. Bạn chỉ có thể xem, không thể thay đổi.</p>
                        </div>
                    @endif
                </form>
            </div>
        </main>
    </div>

    <script>
        // ===== FILTER FUNCTIONS =====
        function toggleFilter(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('filterDropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'block';
            } else {
                // Keep open if typing
                if (event.type === 'keyup') return;
            }
        }

        function closeFilter() {
            document.getElementById('filterDropdown').style.display = 'none';
        }

        function filterProducts() {
            const searchValue = document.getElementById('filterSearch').value.toLowerCase().trim();
            const filterOptions = document.querySelectorAll('.filter-option');

            filterOptions.forEach(option => {
                const productName = option.dataset.productName;
                const matches = productName.includes(searchValue);
                option.style.display = matches ? 'flex' : 'none';

                const cb = option.querySelector('.product-filter-checkbox');
                if (searchValue !== '') {
                    // If searching, auto-check if matches, uncheck if not
                    cb.checked = matches;
                } else {
                    // If clear search, show all (check all filter checkboxes)
                    cb.checked = true;
                }
            });

            applyFilter();
        }

        function toggleAllFilters(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.product-filter-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            applyFilter();
        }

        function applyFilter() {
            const checkboxes = document.querySelectorAll('.product-filter-checkbox');
            const selectedProductIds = new Set();
            
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedProductIds.add(cb.dataset.productId);
                }
            });

            // Show/hide product rows based on filter
            const productRows = document.querySelectorAll('.product-row');
            productRows.forEach(row => {
                const productId = row.dataset.productId;
                if (selectedProductIds.has(productId)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Update "Select All" checkbox state
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            const selectAllCheckbox = document.getElementById('selectAllFilter');
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }

        // Close filter when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('filterDropdown');
            const filterSearch = document.getElementById('filterSearch');
            
            if (dropdown && filterSearch && !dropdown.contains(event.target) && !filterSearch.contains(event.target)) {
                closeFilter();
            }
        });

        // ===== EXISTING FUNCTIONS =====
        function toggleProductRow(checkbox) {
            const productId = checkbox.dataset.productId;
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');
            
            if (checkbox.checked) {
                if (parseFloat(quantityInput.value) === 0) {
                    quantityInput.value = 1;
                    calculateTotal(quantityInput);
                }
            } else {
                quantityInput.value = 0;
                calculateTotal(quantityInput);
            }
        }

        function calculateTotal(input) {
            const row = input.closest('tr');
            const price = parseFloat(input.dataset.price);
            
            // Enforce positive integer: floor the value and ensure it's at least 0
            let quantity = Math.floor(parseFloat(input.value) || 0);
            if (quantity < 0) quantity = 0;
            input.value = quantity; // Update input field with sanitized value

            const total = price * quantity;
            
            const totalCell = row.querySelector('.total-cell');
            totalCell.textContent = total.toLocaleString('vi-VN');
            
            // Auto-check checkbox if quantity > 0
            const checkbox = row.querySelector('.product-checkbox');
            if (quantity > 0 && !checkbox.checked) {
                checkbox.checked = true;
            } else if (quantity === 0 && checkbox.checked) {
                checkbox.checked = false;
            }
            
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                const price = parseFloat(input.dataset.price);
                const quantity = parseFloat(input.value) || 0;
                grandTotal += price * quantity;
            });
            
            document.getElementById('grandTotal').textContent = grandTotal.toLocaleString('vi-VN');
        }

        // Calculate initial total on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateGrandTotal();

            // Prevent Enter key from submitting the form accidentally
            document.getElementById('requestForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const tag = e.target.tagName.toLowerCase();
                    if (tag === 'input' && e.target.type !== 'submit') {
                        e.preventDefault();
                    }
                }
            });

            // Before submitting: disable inputs in hidden rows so they don't overwrite existing orders
            document.getElementById('requestForm').addEventListener('submit', function() {
                document.querySelectorAll('.product-row').forEach(function(row) {
                    // If row is hidden by filter, disable all its inputs so they are NOT sent to server
                    if (row.style.display === 'none') {
                        row.querySelectorAll('input').forEach(function(input) {
                            input.disabled = true;
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
