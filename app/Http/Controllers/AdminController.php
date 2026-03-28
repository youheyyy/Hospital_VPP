<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\MonthlyOrder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Hiển thị dashboard admin
     */
    public function dashboard(Request $request)
    {
        // Lấy tháng từ request hoặc mặc định là tháng hiện tại
        $selectedMonth = $request->input('month', date('m/Y'));

        // Tổng số yêu cầu trong tháng
        $totalRequests = MonthlyOrder::where('month', $selectedMonth)->count();

        // Tổng chi phí (giả định, cần tính toán thực tế)
        $totalCost = 42850000;

        // Sản phẩm được yêu cầu nhiều nhất
        $topProduct = MonthlyOrder::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('month', $selectedMonth)
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'DESC')
            ->first();

        $topProductName = 'Chưa có dữ liệu';
        $topProductQuantity = 0;

        if ($topProduct) {
            $product = Product::find($topProduct->product_id);
            $topProductName = $product ? $product->name : 'Chưa có dữ liệu';
            $topProductQuantity = $topProduct->total_quantity;
        }

        // Phân bổ theo phòng ban (chi phí giả định)
        $departments = Department::all();
        $departmentStats = [];

        foreach ($departments as $dept) {
            $orderCount = MonthlyOrder::where('department_id', $dept->id)
                ->where('month', $selectedMonth)
                ->sum('quantity');

            $departmentStats[] = [
                'name' => $dept->name,
                'value' => $orderCount * 100000, // Giả định giá trị
            ];
        }

        // Yêu cầu gần đây
        $recentRequests = MonthlyOrder::with(['department', 'product'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'selectedMonth',
            'totalRequests',
            'totalCost',
            'topProductName',
            'topProductQuantity',
            'departmentStats',
            'recentRequests'
        ));
    }

    /**
     * Tổng hợp yêu cầu của tất cả khoa
     */
    public function consolidated(Request $request)
    {
        // Lấy tháng được chọn hoặc tháng hiện tại
        $selectedMonth = $request->input('month', date('m/Y'));

        // Lấy category filter
        $selectedCategory = $request->input('category');

        // Lấy tất cả departments
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Lấy tất cả categories
        $categoriesQuery = Category::orderBy('display_order');

        // Nếu có filter category, chỉ lấy category đó
        if ($selectedCategory) {
            $categories = $categoriesQuery->where('id', $selectedCategory)->get();
        } else {
            $categories = $categoriesQuery->get();
        }

        // Lấy tất cả products với orders (CHỈ THÁNG ĐƯỢC CHỌN)
        $productsQuery = Product::with([
            'category',
            'monthlyOrders' => function ($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth)->with('department');
            }
        ])
            ->orderBy('category_id')
            ->orderBy('display_order');

        // Filter theo category nếu có
        if ($selectedCategory) {
            $productsQuery->where('category_id', $selectedCategory);
        }

        $products = $productsQuery->get()->groupBy('category_id');

        // Lấy tất cả categories cho dropdown (không filter)
        $allCategories = Category::orderBy('display_order')->get();

        // Tính tổng cho mỗi category (CHỈ TÍNH THÁNG ĐƯỢC CHỌN cho Bảng Tổng)
        $categoryTotals = [];
        foreach ($products as $categoryId => $categoryProducts) {
            $categoryTotal = 0;
            foreach ($categoryProducts as $product) {
                foreach ($product->monthlyOrders as $order) {
                    if ($order->month == $selectedMonth) {
                        $categoryTotal += $order->quantity * $product->price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        // Tính tổng tất cả (CHỈ THÁNG ĐƯỢC CHỌN)
        $grandTotal = array_sum($categoryTotals);

        return view('admin.consolidated', compact(
            'departments',
            'categories',
            'allCategories',
            'products',
            'selectedMonth',
            'categoryTotals',
            'grandTotal'
        ));
    }

    /**
     * Export consolidated data to Excel
     */
    public function exportConsolidated(Request $request)
    {
        // Lấy tháng được chọn hoặc tháng hiện tại
        $selectedMonth = $request->input('month', date('m/Y'));

        // Lấy tất cả departments
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Lấy tất cả categories (không filter theo category trong export)
        $categories = Category::orderBy('display_order')->get();

        // Lấy tất cả products với orders (CHỈ THÁNG ĐƯỢC CHỌN)
        $products = Product::with([
            'category',
            'monthlyOrders' => function ($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth)->with('department');
            }
        ])
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->get()
            ->groupBy('category_id');

        // Tính tổng cho mỗi category (CHỈ THÁNG ĐƯỢC CHỌN)
        $categoryTotals = [];
        foreach ($products as $categoryId => $categoryProducts) {
            $categoryTotal = 0;
            foreach ($categoryProducts as $product) {
                foreach ($product->monthlyOrders as $order) {
                    if ($order->month == $selectedMonth) {
                        $categoryTotal += $order->quantity * $product->price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        // Tính tổng tất cả
        $grandTotal = array_sum($categoryTotals);

        // Tạo filename
        $filename = 'Tong_hop_VPP_' . str_replace('/', '_', $selectedMonth) . '_' . date('YmdHis') . '.xlsx';

        // Export
        $export = new \App\Exports\ConsolidatedExport(
            $selectedMonth,
            $departments,
            $categories,
            $products,
            $categoryTotals,
            $grandTotal
        );

        return $export->download($filename);
    }

    /**
     * Xuất Excel cho giao diện hiện tại (Bảng Tổng / Tổng Hợp / Phiếu Xuất Kho 1 khoa)
     */
    public function exportSingleConsolidated(Request $request)
    {
        $selectedMonth = $request->input('month', date('m/Y'));
        $tabType = $request->input('tabType', 'bang_tong');
        $deptId = $request->input('deptId');

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('display_order')->get();

        $products = Product::with([
            'category',
            'monthlyOrders' => function ($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth)->with('department');
            }
        ])
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->get()
            ->groupBy('category_id');

        $categoryTotals = [];
        foreach ($products as $categoryId => $categoryProducts) {
            $categoryTotal = 0;
            foreach ($categoryProducts as $product) {
                foreach ($product->monthlyOrders as $order) {
                    if ($order->month == $selectedMonth) {
                        $categoryTotal += $order->quantity * $product->price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        $grandTotal = array_sum($categoryTotals);

        $safeMonth = str_replace('/', '_', $selectedMonth);
        $filename = 'Export_VPP_' . $safeMonth . '.xlsx';

        if ($tabType === 'bang_tong') {
            $filename = 'Bang_tong_VPP_' . $safeMonth . '.xlsx';
        } elseif ($tabType === 'tong_hop') {
            $filename = 'Tong_hop_VPP_' . $safeMonth . '.xlsx';
        } elseif ($tabType === 'phieu_xuat_kho' && $deptId) {
            $dept = $departments->firstWhere('id', $deptId);
            if ($dept) {
                $safeDept = \Illuminate\Support\Str::slug($dept->name, '_');
                $filename = 'Phieu_xuat_kho_' . mb_strtoupper($safeDept) . '_' . $safeMonth . '.xlsx';
            }
        }

        $export = new \App\Exports\ConsolidatedExport(
            $selectedMonth,
            $departments,
            $categories,
            $products,
            $categoryTotals,
            $grandTotal,
            $tabType,
            $deptId
        );

        return $export->download($filename);
    }

    /**
     * Print/PDF view for consolidated data
     */
    public function printConsolidated(Request $request)
    {
        // Lấy tháng được chọn hoặc tháng hiện tại
        $selectedMonth = $request->input('month', date('m/Y'));
        $tabType = $request->input('tabType', 'tong_hop');
        $deptId = $request->input('deptId');

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        if ($tabType === 'phieu_xuat_kho' && $deptId) {
            $department = $departments->firstWhere('id', $deptId);
            if ($department) {
                $orders = \App\Models\MonthlyOrder::where('department_id', $department->id)
                    ->where('month', $selectedMonth)
                    ->with(['product.category'])
                    ->get()
                    ->groupBy('product.category.name');

                $totalAmount = \App\Models\MonthlyOrder::where('department_id', $department->id)
                    ->where('month', $selectedMonth)
                    ->with('product')
                    ->get()
                    ->sum(function ($order) {
                        return $order->quantity * $order->product->price;
                    });

                return view('department.department-print', compact(
                    'department',
                    'orders',
                    'selectedMonth',
                    'totalAmount'
                ));
            }
        }

        $categories = Category::where('is_active', true)->orderBy('display_order')->get();

        $products = Product::where('is_active', true)
            ->with([
                'category',
                'monthlyOrders' => function ($query) use ($selectedMonth) {
                    $query->where('month', $selectedMonth)
                        ->with('department');
                }
            ])
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->get()
            ->groupBy('category_id');

        if ($tabType === 'bang_tong') {
            return view('admin.print-bang-tong', compact(
                'departments',
                'categories',
                'products',
                'selectedMonth'
            ));
        }

        // Tính tổng cho mỗi category
        $categoryTotals = [];
        foreach ($products as $categoryId => $categoryProducts) {
            $categoryTotal = 0;
            foreach ($categoryProducts as $product) {
                foreach ($product->monthlyOrders as $order) {
                    $categoryTotal += $order->quantity * $product->price;
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        // Tính tổng tất cả
        $grandTotal = array_sum($categoryTotals);

        // Chuẩn bị dữ liệu cho Phiếu Xuất Kho (Grouped by Department) - legacy if needed within tong-hop document, though currently hidden.
        $departmentOrders = [];
        foreach ($departments as $dept) {
            $deptOrders = [];
            foreach ($products as $categoryId => $categoryProducts) {
                $categoryData = [
                    'category' => $categories->firstWhere('id', $categoryId),
                    'orders' => []
                ];

                foreach ($categoryProducts as $product) {
                    $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                    if ($order && $order->quantity > 0) {
                        $categoryData['orders'][] = [
                            'product' => $product,
                            'order' => $order,
                            'total' => $order->quantity * $product->price
                        ];
                    }
                }

                if (!empty($categoryData['orders'])) {
                    $deptOrders[] = $categoryData;
                }
            }

            if (!empty($deptOrders)) {
                $departmentOrders[$dept->id] = [
                    'department' => $dept,
                    'sections' => $deptOrders,
                    'total' => collect($deptOrders)->sum(fn($s) => collect($s['orders'])->sum('total'))
                ];
            }
        }

        return view('admin.consolidated-print', compact(
            'departments',
            'categories',
            'products',
            'selectedMonth',
            'categoryTotals',
            'grandTotal',
            'departmentOrders'
        ));
    }
    // public function updateNote(Request $request)
    // {
    public function updateNote(Request $request)
    {
        Log::info('=== UPDATE NOTE REQUEST ===');
        Log::info('Product ID: ' . $request->product_id);
        Log::info('Month: ' . $request->month);
        Log::info('Note: ' . $request->note);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'month' => 'required',
            'note' => 'nullable|string',
        ]);
        // Enable query logging
        DB::enableQueryLog();

        // Get the orders first to see what we're updating
        $orders = MonthlyOrder::where('product_id', $request->product_id)
            ->where('month', $request->month)
            ->get();

        Log::info('Found orders: ' . $orders->count());

        // Update each order individually to ensure it works
        $affected = 0;
        foreach ($orders as $order) {
            /** @var \App\Models\MonthlyOrder $order */

            Log::info("Updating order ID: {$order->id}, current note: '{$order->admin_notes}'");
            $order->admin_notes = $request->note;
            $order->save();
            $affected++;
            Log::info("After save, note is: '{$order->admin_notes}'");
        }

        // Log the queries
        $queries = DB::getQueryLog();
        Log::info('SQL Queries executed:');
        foreach ($queries as $query) {
            Log::info(json_encode($query));
        }

        Log::info('Total rows updated: ' . $affected);

        return response()->json(['success' => true, 'affected' => $affected]);
    }



    /**
     * AJAX cập nhật số lượng từ Bảng Tổng
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'department_id' => 'required|exists:departments,id',
            'month' => 'required',
            'quantity' => 'nullable|numeric|min:0',
        ]);

        $order = MonthlyOrder::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'department_id' => $request->department_id,
                'month' => $request->month,
            ],
            [
                'quantity' => $request->quantity ?? 0,
            ]
        );

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    /**
     * Hiển thị danh sách ngân sách (Admin)
     */
    public function budgets(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        
        $departments = Department::where('is_active', true)
            ->with(['budgets' => function($query) use ($selectedYear) {
                $query->where('year', $selectedYear);
            }])
            ->orderBy('name')
            ->get();

        // Tính tổng ngân sách
        $totalBudget = \App\Models\DepartmentBudget::where('year', $selectedYear)->sum('total_budget');
        $totalUsed = \App\Models\DepartmentBudget::where('year', $selectedYear)->sum('used_budget');
        $totalRemaining = \App\Models\DepartmentBudget::where('year', $selectedYear)->sum('remaining_budget');

        return view('admin.budgets.index', compact(
            'departments',
            'selectedYear',
            'totalBudget',
            'totalUsed',
            'totalRemaining'
        ));
    }

    /**
     * Tạo hoặc cập nhật ngân sách (Admin)
     */
    public function storeBudget(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'year' => 'required|integer|min:2020|max:2100',
            'total_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $budget = \App\Models\DepartmentBudget::updateOrCreate(
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
     * Xóa ngân sách (Admin)
     */
    public function destroyBudget(\App\Models\DepartmentBudget $budget)
    {
        $budget->delete();
        return redirect()->back()->with('success', 'Ngân sách đã được xóa!');
    }

    /**
     * Tính lại ngân sách đã sử dụng (Admin)
     */
    public function recalculateBudget(\App\Models\DepartmentBudget $budget)
    {
        $budget->recalculateUsedBudget();
        return redirect()->back()->with('success', 'Đã tính lại ngân sách thành công!');
    }

}
