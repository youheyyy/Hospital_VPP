<!DOCTYPE html>
<html class="light" lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $department->name }} - Lịch sử yêu cầu</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .excel-table {
            border-collapse: collapse;
            width: 100%;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #d1d5db;
            padding: 8px 12px;
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

        .total-row {
            background: #fef3c7;
            font-weight: bold;
        }

        .editable-quantity {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .editable-quantity:hover {
            background-color: #f0f9ff;
        }

        .editable-notes {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .editable-notes:hover {
            background-color: #f0f9ff;
        }

        .quantity-input-edit {
            width: 100%;
            text-align: right;
            border: 2px solid #3b82f6;
            padding: 4px 8px;
            font-size: 0.875rem;
        }

        .notes-input-edit {
            width: 100%;
            border: 2px solid #3b82f6;
            padding: 4px 8px;
            font-size: 0.875rem;
            min-height: 60px;
        }
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
                <a href="{{ route('department.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Yêu cầu VPP</span>
                </a>
                <a href="{{ route('department.history') }}"
                    class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg">
                    <span class="material-symbols-outlined">history</span>
                    <span>Lịch sử yêu cầu</span>
                </a>
            </nav>
            <div class="p-4 border-t">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ mb_strtoupper(mb_substr($department->name, 0, 2, 'UTF-8'), 'UTF-8') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ $department->name }}</p>
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
            <header class="bg-white border-b px-8 py-4">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Lịch sử yêu cầu</h1>
                        <p class="text-sm text-gray-500">{{ $department->name }} - Tháng {{ $selectedMonth }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <form method="GET" action="{{ route('department.history') }}">
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
                    </div>
                </div>

                <!-- Budget Progress Bar -->
                @php
                    $parts = explode('/', $selectedMonth);
                    $year = isset($parts[1]) ? (int)$parts[1] : date('Y');
                    $budget = \App\Models\DepartmentBudget::where('department_id', $department->id)
                        ->where('year', $year)
                        ->first();
                @endphp
                @if($budget)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">account_balance_wallet</span>
                                <span class="text-sm font-semibold text-gray-700">Ngân sách năm {{ $year }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Còn lại</div>
                                <div class="text-sm font-bold {{ $budget->remaining_budget < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($budget->remaining_budget, 0, ',', '.') }} VNĐ
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                @php
                                    $usagePercentage = $budget->total_budget > 0 ? ($budget->used_budget / $budget->total_budget) * 100 : 0;
                                    $barColor = $usagePercentage > 90 ? 'bg-red-600' : ($usagePercentage > 70 ? 'bg-orange-500' : 'bg-green-500');
                                    $barWidth = min($usagePercentage, 100);
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="h-3 rounded-full {{ $barColor }} transition-all duration-300" 
                                         style="width: {{ $barWidth }}%"></div>
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-gray-700 min-w-[60px] text-right">
                                {{ number_format($usagePercentage, 1) }}%
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-600">
                            <span>Đã dùng: {{ number_format($budget->used_budget, 0, ',', '.') }} VNĐ</span>
                            <span>Tổng: {{ number_format($budget->total_budget, 0, ',', '.') }} VNĐ</span>
                        </div>
                        @if($usagePercentage > 90)
                            <div class="mt-2 text-xs text-red-600 font-medium">
                                ⚠️ Cảnh báo: Ngân sách sắp hết!
                            </div>
                        @elseif($usagePercentage > 70)
                            <div class="mt-2 text-xs text-orange-600 font-medium">
                                ⚠️ Lưu ý: Đã sử dụng hơn 70% ngân sách
                            </div>
                        @endif
                    </div>
                @endif
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8">
                @if(isset($canEdit) && !$canEdit)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 mx-auto max-w-7xl animate-pulse">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="material-symbols-outlined text-yellow-600">lock_clock</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-700">
                                    Đã quá hạn chỉnh sửa cho tháng này (Sau ngày 5 của tháng tiếp theo). Bạn chỉ có thể xem lịch sử.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($orders->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">inbox</span>
                        <p class="mt-4 text-gray-500">Chưa có yêu cầu nào trong tháng này</p>
                        @if($canEdit ?? true)
                            <a href="{{ route('department.index') }}"
                                class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Tạo yêu cầu mới
                            </a>
                        @endif
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <table class="excel-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Tên hàng</th>
                                    <th style="width: 100px;">ĐVT</th>
                                    <th style="width: 120px;">Số lượng</th>
                                    <th style="width: 130px;">Đơn giá</th>
                                    <th style="width: 150px;">Thành tiền</th>
                                    <th style="width: 200px;">Ghi chú</th>
                                    <th style="width: 200px;">Ghi chú Admin</th>
                                    <th style="width: 150px;">Ngày tạo</th>
                                    <th style="width: 150px;">Ngày cập nhật</th>
                                    <th style="width: 100px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $stt = 0; @endphp
                                @foreach($orders as $categoryName => $categoryOrders)
                                    <!-- Category Header -->
                                    <tr class="category-header">
                                        <td colspan="12">{{ mb_strtoupper($categoryName, 'UTF-8') }}</td>
                                    </tr>

                                    <!-- Products -->
                                    @foreach($categoryOrders as $order)
                                        @php $stt++; @endphp
                                        <tr data-order-id="{{ $order->id }}" data-product-price="{{ $order->product->price }}" id="order-row-{{ $order->id }}">
                                            <td class="text-center text-sm text-gray-600">{{ $stt }}</td>
                                            <td class="text-sm font-medium">{{ $order->product->name }}</td>
                                            <td class="text-center text-sm">{{ $order->product->unit }}</td>
                                            <td class="text-right text-sm {{ ($canEdit ?? true) ? 'editable-quantity' : '' }}" 
                                                data-order-id="{{ $order->id }}" 
                                                data-current-quantity="{{ $order->quantity }}"
                                                @if($canEdit ?? true)
                                                    ondblclick="editQuantity(this)"
                                                    title="Double click để chỉnh sửa"
                                                @endif>
                                                <span class="quantity-display">{{ number_format($order->quantity, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-right text-sm">{{ number_format($order->product->price, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right text-sm font-semibold total-cell">
                                                {{ number_format($order->quantity * $order->product->price, 0, ',', '.') }}</td>
                                            <td class="text-sm text-gray-600 {{ ($canEdit ?? true) ? 'editable-notes' : '' }}"
                                                data-order-id="{{ $order->id }}"
                                                data-current-notes="{{ $order->notes ?? '' }}"
                                                @if($canEdit ?? true)
                                                    ondblclick="editNotes(this)"
                                                    title="Double click để chỉnh sửa"
                                                @endif>
                                                <span class="notes-display">{{ $order->notes ?? '' }}</span>
                                            </td>
                                            <td class="text-sm text-blue-700 bg-blue-50">{{ $order->admin_notes ?? '' }}</td>
                                            <td class="text-center text-sm text-gray-600">
                                                {{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center text-sm text-gray-600">
                                                {{ $order->updated_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center">
                                                @if($canEdit ?? true)
                                                    <button onclick="deleteOrder({{ $order->id }})" 
                                                        class="px-3 py-1 text-xs text-red-600 hover:text-white hover:bg-red-600 border border-red-600 rounded transition-colors"
                                                        title="Xóa dòng này">
                                                        <span class="material-symbols-outlined text-sm" style="font-size: 16px;">delete</span>
                                                    </button>
                                                @else
                                                    <span class="material-symbols-outlined text-gray-400 text-sm" title="Đã khóa">lock</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach

                                <!-- Total Row -->
                                <tr class="total-row">
                                    <td colspan="5" class="text-right font-bold">TỔNG CỘNG:</td>
                                    <td class="text-right font-bold" id="grandTotalDisplay">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                                    <td colspan="6"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <button onclick="printDirect(this)" type="button"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-sm">print</span>
                            In yêu cầu
                        </button>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Hidden iframe for printing -->
    <iframe id="printFrame" style="display:none;"></iframe>

    <script>
        function printDirect(btn) {
            const month = "{{ $selectedMonth }}";
            const printUrl = "{{ route('department.history.print') }}?month=" + encodeURIComponent(month);
            const printFrame = document.getElementById('printFrame');
            const originalContent = btn.innerHTML;

            // Show loading state
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

        let currentEditingCell = null;

        function editQuantity(cell) {
            // Prevent multiple edits at once
            if (currentEditingCell) {
                return;
            }

            currentEditingCell = cell;
            const orderId = cell.dataset.orderId;
            const currentQuantity = cell.dataset.currentQuantity;
            const displaySpan = cell.querySelector('.quantity-display');

            // Create input element
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'quantity-input-edit';
            input.value = currentQuantity;
            input.min = '0';
            input.step = '1';

            // Replace display with input
            displaySpan.style.display = 'none';
            cell.appendChild(input);
            input.focus();
            input.select();

            // Handle save on Enter or blur
            const saveEdit = async () => {
                const newQuantity = parseInt(input.value) || 0;
                
                if (newQuantity === parseInt(currentQuantity)) {
                    // No change, just cancel
                    cancelEdit();
                    return;
                }

                // Show loading state
                input.disabled = true;
                cell.style.opacity = '0.6';

                try {
                    const response = await fetch(`/department/order/${orderId}/update-quantity`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ quantity: newQuantity })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Update display
                        displaySpan.textContent = newQuantity.toLocaleString('vi-VN');
                        cell.dataset.currentQuantity = newQuantity;
                        
                        // Update total cell
                        const row = cell.closest('tr');
                        const price = parseFloat(row.dataset.productPrice);
                        const totalCell = row.querySelector('.total-cell');
                        totalCell.textContent = (newQuantity * price).toLocaleString('vi-VN');
                        
                        // Update grand total
                        updateGrandTotal();
                        
                        // Show success feedback
                        cell.style.backgroundColor = '#d1fae5';
                        setTimeout(() => {
                            cell.style.backgroundColor = '';
                        }, 1000);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi cập nhật số lượng');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật số lượng');
                }

                // Clean up
                input.remove();
                displaySpan.style.display = '';
                cell.style.opacity = '';
                currentEditingCell = null;
            };

            const cancelEdit = () => {
                input.remove();
                displaySpan.style.display = '';
                currentEditingCell = null;
            };

            // Event listeners
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveEdit();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelEdit();
                }
            });

            input.addEventListener('blur', () => {
                setTimeout(saveEdit, 100);
            });
        }

        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('tr[data-order-id]').forEach(row => {
                const quantity = parseFloat(row.querySelector('.editable-quantity').dataset.currentQuantity) || 0;
                const price = parseFloat(row.dataset.productPrice) || 0;
                grandTotal += quantity * price;
            });
            
            const totalCell = document.getElementById('grandTotalDisplay');
            if (totalCell) {
                totalCell.textContent = grandTotal.toLocaleString('vi-VN');
            }
        }

        function editNotes(cell) {
            // Prevent multiple edits at once
            if (currentEditingCell) {
                return;
            }

            currentEditingCell = cell;
            const orderId = cell.dataset.orderId;
            const currentNotes = cell.dataset.currentNotes;
            const displaySpan = cell.querySelector('.notes-display');

            // Create textarea element
            const textarea = document.createElement('textarea');
            textarea.className = 'notes-input-edit';
            textarea.value = currentNotes;

            // Replace display with textarea
            displaySpan.style.display = 'none';
            cell.appendChild(textarea);
            textarea.focus();
            textarea.select();

            // Handle save on Ctrl+Enter or blur
            const saveEdit = async () => {
                const newNotes = textarea.value.trim();
                
                if (newNotes === currentNotes) {
                    // No change, just cancel
                    cancelEdit();
                    return;
                }

                // Show loading state
                textarea.disabled = true;
                cell.style.opacity = '0.6';

                try {
                    const response = await fetch(`/department/order/${orderId}/update-notes`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ notes: newNotes })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Update display
                        displaySpan.textContent = newNotes;
                        cell.dataset.currentNotes = newNotes;
                        
                        // Show success feedback
                        cell.style.backgroundColor = '#d1fae5';
                        setTimeout(() => {
                            cell.style.backgroundColor = '';
                        }, 1000);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi cập nhật ghi chú');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật ghi chú');
                }

                // Clean up
                textarea.remove();
                displaySpan.style.display = '';
                cell.style.opacity = '';
                currentEditingCell = null;
            };

            const cancelEdit = () => {
                textarea.remove();
                displaySpan.style.display = '';
                currentEditingCell = null;
            };

            // Event listeners
            textarea.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.ctrlKey) {
                    // Ctrl+Enter to save
                    e.preventDefault();
                    saveEdit();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelEdit();
                }
            });

            textarea.addEventListener('blur', () => {
                setTimeout(saveEdit, 100);
            });
        }

        async function deleteOrder(orderId) {
            if (!confirm('Bạn có chắc chắn muốn xóa dòng này không?')) {
                return;
            }

            const row = document.getElementById(`order-row-${orderId}`);
            if (!row) return;

            // Show loading state
            row.style.opacity = '0.5';

            try {
                const response = await fetch(`/department/order/${orderId}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Fade out animation
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    
                    setTimeout(() => {
                        row.remove();
                        updateGrandTotal();
                        
                        // Check if table is empty
                        const remainingRows = document.querySelectorAll('tr[data-order-id]');
                        if (remainingRows.length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi xóa dòng này');
                    row.style.opacity = '1';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa dòng này');
                row.style.opacity = '1';
            }
        }
    </script>
</body>

</html>