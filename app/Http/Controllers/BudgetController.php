<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * Hiển thị danh sách ngân sách (SuperAdmin)
     */
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        
        $departments = Department::where('is_active', true)
            ->with(['budgets' => function($query) use ($selectedYear) {
                $query->where('year', $selectedYear);
            }])
            ->orderBy('name')
            ->get();

        // Tính tổng ngân sách
        $totalBudget = DepartmentBudget::where('year', $selectedYear)->sum('total_budget');
        $totalUsed = DepartmentBudget::where('year', $selectedYear)->sum('used_budget');
        $totalRemaining = DepartmentBudget::where('year', $selectedYear)->sum('remaining_budget');

        return view('superadmin.budgets.index', compact(
            'departments',
            'selectedYear',
            'totalBudget',
            'totalUsed',
            'totalRemaining'
        ));
    }

    /**
     * Tạo hoặc cập nhật ngân sách (SuperAdmin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'year' => 'required|integer|min:2020|max:2100',
            'total_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $budget = DepartmentBudget::updateOrCreate(
            [
                'department_id' => $validated['department_id'],
                'year' => $validated['year'],
            ],
            [
                'total_budget' => $validated['total_budget'],
                'remaining_budget' => $validated['total_budget'],
                'notes' => $validated['notes'],
            ]
        );

        // Tính lại ngân sách đã sử dụng từ các đơn hàng hiện có
        $budget->recalculateUsedBudget();

        return redirect()->back()->with('success', 'Ngân sách đã được cập nhật thành công!');
    }

    /**
     * Xóa ngân sách (SuperAdmin)
     */
    public function destroy(DepartmentBudget $budget)
    {
        $budget->delete();
        return redirect()->back()->with('success', 'Ngân sách đã được xóa!');
    }

    /**
     * Tính lại ngân sách đã sử dụng (SuperAdmin)
     */
    public function recalculate(DepartmentBudget $budget)
    {
        $budget->recalculateUsedBudget();
        return redirect()->back()->with('success', 'Đã tính lại ngân sách thành công!');
    }
}
