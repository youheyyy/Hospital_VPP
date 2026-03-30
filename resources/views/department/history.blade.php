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
        body { font-family: 'Inter', sans-serif; }
        .excel-table { border-collapse: collapse; width: 100%; border: 1px solid #e5e7eb; }
        .excel-table th, .excel-table td { border: 1px solid #e5e7eb; padding: 10px 12px; }
        
        /* Toast Notification Styles */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .toast { 
            background: white; border-left: 4px solid #3b82f6; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); 
            padding: 16px 20px; border-radius: 8px; margin-bottom: 12px; min-width: 300px;
            display: flex; align-items: center; gap: 12px; transform: translateX(120%); transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .toast.show { transform: translateX(0); }
        .toast-error { border-left-color: #ef4444; }
        .toast-success { border-left-color: #10b981; }

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
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Lịch sử yêu cầu</h1>
                    <p class="text-sm text-gray-500">{{ $department->name }} - Tháng {{ $selectedMonth }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-100 rounded-lg p-1 border border-gray-200">
                        <button onclick="changeMonth(-1)" 
                            class="p-2 hover:bg-white hover:text-blue-600 rounded-md transition-all group flex items-center justify-center"
                            id="btnPrevMonth" title="Tháng trước">
                            <span class="material-symbols-outlined text-lg">chevron_left</span>
                        </button>
                        
                        <div class="relative px-4 py-1.5 flex items-center gap-2 border-x border-gray-200 group">
                            <span class="text-sm font-bold text-gray-700">Tháng</span>
                            <div class="flex items-center">
                                <input type="text" id="monthInput" value="{{ $selectedMonth }}" 
                                    class="bg-white border border-gray-300 rounded px-2 py-1 text-sm font-bold text-blue-600 focus:ring-2 focus:ring-blue-500 w-24 text-center transition-all"
                                    placeholder="mm/yyyy"
                                    onkeydown="if(event.key === 'Enter') validateAndGo(this.value)">
                                <button onclick="validateAndGo(document.getElementById('monthInput').value)" 
                                    class="ml-1 p-1 text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Đi tới tháng này">
                                    <span class="material-symbols-outlined text-sm">send</span>
                                </button>
                            </div>
                            <button onclick="document.getElementById('monthSelector').showPicker()" 
                                class="p-1 hover:text-blue-600 transition-colors" title="Chọn từ lịch">
                                <span class="material-symbols-outlined text-sm">calendar_month</span>
                            </button>
                            <input type="month" id="monthSelector" class="absolute inset-0 opacity-0 pointer-events-none" 
                                value="{{ \Carbon\Carbon::createFromFormat('m/Y', $selectedMonth)->format('Y-m') }}"
                                onchange="onMonthSelected(this.value)">
                        </div>

                        <button onclick="changeMonth(1)" 
                            class="p-2 hover:bg-white hover:text-blue-600 rounded-md transition-all group flex items-center justify-center"
                            id="btnNextMonth" title="Tháng sau">
                            <span class="material-symbols-outlined text-lg">chevron_right</span>
                        </button>
                    </div>
                </div>
            </header>

            <div id="toast-container"></div>

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
                                    Đã quá hạn chỉnh sửa cho yêu cầu tháng {{ $selectedMonth }} (Sau 23:59:59 ngày 25 hàng tháng). Bạn chỉ có thể xem lịch sử.
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
                                            <td class="text-sm px-3 py-2">
                                                @if($order->admin_notes)
                                                    @php
                                                        $adminParts = explode('|||', $order->admin_notes);
                                                        $shared = trim($adminParts[0]);
                                                        $private = isset($adminParts[1]) ? trim($adminParts[1]) : '';
                                                    @endphp
                                                    <div class="flex flex-col gap-1">
                                                        @if($shared)
                                                            <div class="text-blue-800 bg-blue-50 px-2 py-1 rounded text-xs italic" title="Ghi chú chung">
                                                                <span class="font-bold">Tổng hợp:</span> {{ $shared }}
                                                            </div>
                                                        @endif
                                                        @if($private)
                                                            <div class="text-red-800 bg-red-50 px-2 py-1 rounded text-xs font-medium" title="Đã chỉnh sửa số lượng">
                                                                <span class="font-bold">Đã chỉnh sửa:</span> {{ $private }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
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
        const EARLIEST_MONTH = "{{ $earliestMonth }}"; // m/Y
        const LATEST_MONTH = "{{ $latestMonth }}"; // m/Y
        const SELECTED_MONTH = "{{ $selectedMonth }}"; // m/Y

        function getMonthData(mStr) {
            const parts = mStr.split('/');
            return { month: parseInt(parts[0]), year: parseInt(parts[1]) };
        }

        function formatDate(m, y) {
            return `${m.toString().padStart(2, '0')}/${y}`;
        }

        function isBeforeEarliest(mStr) {
            const current = getMonthData(mStr);
            const earliest = getMonthData(EARLIEST_MONTH);
            if (current.year < earliest.year) return true;
            if (current.year === earliest.year && current.month < earliest.month) return true;
            return false;
        }

        function isAfterLatest(mStr) {
            const current = getMonthData(mStr);
            const latest = getMonthData(LATEST_MONTH);
            if (current.year > latest.year) return true;
            if (current.year === latest.year && current.month > latest.month) return true;
            return false;
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type} flex items-center gap-3`;
            
            const icon = type === 'error' ? 'report' : (type === 'success' ? 'check_circle' : 'info');
            const iconColor = type === 'error' ? 'text-red-500' : (type === 'success' ? 'text-green-500' : 'text-blue-500');

            toast.innerHTML = `
                <span class="material-symbols-outlined ${iconColor}">${icon}</span>
                <span class="text-sm font-medium text-gray-700">${message}</span>
            `;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function changeMonth(delta) {
            const current = getMonthData(SELECTED_MONTH);
            let m = current.month + delta;
            let y = current.year;

            if (m > 12) { m = 1; y++; }
            if (m < 1) { m = 12; y--; }

            const newMonthStr = formatDate(m, y);
            
            if (isBeforeEarliest(newMonthStr)) {
                showToast(`Dữ liệu của khoa bắt đầu từ tháng ${EARLIEST_MONTH}. Bạn không thể quay lại xa hơn tháng này.`, 'error');
                return;
            }

            if (isAfterLatest(newMonthStr)) {
                showToast(`Tháng ${newMonthStr} là tháng tương lai. Bạn chỉ có thể xem lịch sử tối đa đến tháng ${LATEST_MONTH}.`, 'error');
                return;
            }

            window.location.href = `{{ route('department.history') }}?month=${encodeURIComponent(newMonthStr)}`;
        }

        function onMonthSelected(val) {
            // val is YYYY-MM from <input type="month">
            const parts = val.split('-');
            const newMonthStr = `${parts[1]}/${parts[0]}`;
            validateAndGo(newMonthStr);
        }

        function validateAndGo(mStr) {
            // Check format mm/yyyy
            const regex = /^(0[1-9]|1[0-2])\/\d{4}$/;
            if (!regex.test(mStr)) {
                showToast("Vui lòng nhập tháng theo định dạng mm/yyyy (ví dụ: 03/2026)", 'error');
                return;
            }

            if (isBeforeEarliest(mStr)) {
                showToast(`Dữ liệu của khoa bắt đầu từ tháng ${EARLIEST_MONTH}. Bạn không thể quay lại xa hơn tháng này.`, 'error');
                return;
            }

            if (isAfterLatest(mStr)) {
                showToast(`Bạn đã chọn tháng ${mStr}. Đây là tháng tương lai. Lịch sử chỉ cho phép xem tối đa đến tháng ${LATEST_MONTH}.`, 'error');
                return;
            }

            window.location.href = `{{ route('department.history') }}?month=${encodeURIComponent(mStr)}`;
        }

        function updateNavButtons() {
            const btnPrev = document.getElementById('btnPrevMonth');
            const btnNext = document.getElementById('btnNextMonth');
            
            // Disable prev if at earliest
            if (SELECTED_MONTH === EARLIEST_MONTH) {
                btnPrev.classList.add('opacity-30', 'cursor-not-allowed');
                btnPrev.onclick = null;
            }

            // Disable next if at latest
            if (SELECTED_MONTH === LATEST_MONTH) {
                btnNext.classList.add('opacity-30', 'cursor-not-allowed');
                btnNext.onclick = null;
            }
        }

        document.addEventListener('DOMContentLoaded', updateNavButtons);

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