@extends('layouts.superadmin')

@section('title', 'Quản Lý Ngân Sách')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Quản Lý Ngân Sách Khoa Phòng</h1>
            <p class="text-gray-500 text-sm">Thiết lập và theo dõi ngân sách cho các khoa phòng</p>
        </div>
        <button onclick="openAddBudgetModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg flex items-center gap-2 transition">
            <span class="material-symbols-outlined">add</span>
            Thêm Ngân Sách
        </button>
    </div>

    <!-- Year Filter -->
    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-100">
        <form method="GET" action="{{ route('superadmin.budgets.index') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Năm:</label>
            <select name="year" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @for($y = date('Y') - 2; $y <= date('Y') + 5; $y++)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tổng Ngân Sách</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($totalBudget, 0, ',', '.') }} VNĐ</h3>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">trending_down</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Đã Sử Dụng</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($totalUsed, 0, ',', '.') }} VNĐ</h3>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">savings</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Còn Lại</p>
                    <h3 class="text-2xl font-bold text-slate-900">{{ number_format($totalRemaining, 0, ',', '.') }} VNĐ</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Budgets Table -->
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khoa Phòng</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng Ngân Sách</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đã Sử Dụng</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Còn Lại</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tỷ Lệ</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($departments as $dept)
                        @php
                            $budget = $dept->budgets->first();
                            $percentage = $budget && $budget->total_budget > 0 
                                ? ($budget->used_budget / $budget->total_budget) * 100 
                                : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="size-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($dept->code, 0, 2)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $dept->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $dept->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                {{ $budget ? number_format($budget->total_budget, 0, ',', '.') : '0' }} VNĐ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                {{ $budget ? number_format($budget->used_budget, 0, ',', '.') : '0' }} VNĐ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $budget && $budget->remaining_budget < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $budget ? number_format($budget->remaining_budget, 0, ',', '.') : '0' }} VNĐ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $barColorClass = $percentage > 90 ? 'bg-red-600' : ($percentage > 70 ? 'bg-orange-500' : 'bg-green-500');
                                    $barWidth = min($percentage, 100);
                                @endphp
                                <div class="flex flex-col items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                                        {{-- CSS linter ignore --}}
                                        <div class="h-2 rounded-full {{ $barColorClass }}" 
                                             style="width: {{ $barWidth }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600">{{ number_format($percentage, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <button type="button" data-action="edit" data-dept-id="{{ $dept->id }}" data-dept-name="{{ $dept->name }}" data-budget="{{ $budget ? $budget->total_budget : 0 }}" data-notes="{{ $budget ? $budget->notes : '' }}"
                                        class="text-blue-600 hover:text-blue-900 mr-3 edit-budget-btn">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </button>
                                @if($budget)
                                <button type="button" data-budget-id="{{ $budget->id }}" 
                                        class="text-green-600 hover:text-green-900 mr-3 recalculate-btn" 
                                        title="Tính lại ngân sách">
                                    <span class="material-symbols-outlined text-xl">refresh</span>
                                </button>
                                <form action="{{ route('superadmin.budgets.destroy', $budget) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa ngân sách này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <span class="material-symbols-outlined text-xl">delete</span>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Chưa có dữ liệu ngân sách
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Budget Modal -->
<div id="budgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h2 id="modalTitle" class="text-xl font-bold text-gray-800 mb-6">Thêm Ngân Sách</h2>
        <form action="{{ route('superadmin.budgets.store') }}" method="POST">
            @csrf
            <input type="hidden" id="budgetDeptId" name="department_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Khoa Phòng</label>
                <select id="departmentSelect" name="department_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Chọn khoa phòng --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Năm</label>
                <input type="number" name="year" value="{{ $selectedYear }}" min="2020" max="2100" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tổng Ngân Sách (VNĐ)</label>
                <input type="number" id="totalBudget" name="total_budget" min="0" step="1000" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ghi Chú</label>
                <textarea id="budgetNotes" name="notes" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeBudgetModal()" 
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
                    Hủy
                </button>
                <button type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Event delegation for edit buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-budget-btn')) {
        const btn = e.target.closest('.edit-budget-btn');
        const deptId = btn.dataset.deptId;
        const deptName = btn.dataset.deptName;
        const budget = btn.dataset.budget;
        const notes = btn.dataset.notes;
        editBudget(deptId, deptName, budget, notes);
    }
    
    if (e.target.closest('.recalculate-btn')) {
        const btn = e.target.closest('.recalculate-btn');
        const budgetId = btn.dataset.budgetId;
        recalculateBudget(budgetId);
    }
});

function openAddBudgetModal() {
    document.getElementById('modalTitle').textContent = 'Thêm Ngân Sách';
    document.getElementById('departmentSelect').disabled = false;
    document.getElementById('departmentSelect').style.backgroundColor = '';
    document.getElementById('departmentSelect').style.cursor = '';
    document.getElementById('departmentSelect').value = '';
    document.getElementById('totalBudget').value = '';
    document.getElementById('budgetNotes').value = '';
    document.getElementById('budgetModal').classList.remove('hidden');
}

function editBudget(deptId, deptName, totalBudget, notes) {
    document.getElementById('modalTitle').textContent = 'Chỉnh Sửa Ngân Sách - ' + deptName;
    document.getElementById('departmentSelect').value = deptId;
    // Không disable để giá trị được gửi đi, nhưng thêm readonly style
    document.getElementById('departmentSelect').disabled = false;
    document.getElementById('departmentSelect').style.backgroundColor = '#f3f4f6';
    document.getElementById('departmentSelect').style.cursor = 'not-allowed';
    // Ngăn thay đổi giá trị
    document.getElementById('departmentSelect').addEventListener('mousedown', function(e) {
        if (this.style.backgroundColor === 'rgb(243, 244, 246)') {
            e.preventDefault();
        }
    });
    document.getElementById('totalBudget').value = totalBudget;
    document.getElementById('budgetNotes').value = notes;
    document.getElementById('budgetModal').classList.remove('hidden');
}

function closeBudgetModal() {
    document.getElementById('budgetModal').classList.add('hidden');
}

function recalculateBudget(budgetId) {
    if (confirm('Bạn có chắc muốn tính lại ngân sách từ các đơn hàng hiện có?')) {
        fetch(`/superadmin/budgets/${budgetId}/recalculate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Close modal when clicking outside
document.getElementById('budgetModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBudgetModal();
    }
});
</script>
@endsection
