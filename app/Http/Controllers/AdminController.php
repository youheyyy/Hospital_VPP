<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\MonthlyOrder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Hiển thị dashboard admin
     */
    public function dashboard()
    {
        // Lấy tháng hiện tại
        $currentMonth = date('m/Y');

        // Tổng số yêu cầu trong tháng
        $totalRequests = MonthlyOrder::where('month', $currentMonth)->count();

        // Tổng chi phí (giả định, cần tính toán thực tế)
        $totalCost = 42850000;

        // Sản phẩm được yêu cầu nhiều nhất
        $topProduct = MonthlyOrder::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('month', $currentMonth)
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
                ->where('month', $currentMonth)
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
     * Print/PDF view for consolidated data
     */
    public function printConsolidated(Request $request)
    {
        // Lấy tháng được chọn hoặc tháng hiện tại
        $selectedMonth = $request->input('month', date('m/Y'));

        // Lấy tất cả departments active
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Lấy tất cả categories active
        $categories = Category::where('is_active', true)->orderBy('display_order')->get();

        // Lấy tất cả products với orders của tháng (Eager load to avoid N+1)
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

        // Chuẩn bị dữ liệu cho Phiếu Xuất Kho (Grouped by Department)
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
        \Log::info('=== UPDATE NOTE REQUEST ===');
        \Log::info('Product ID: ' . $request->product_id);
        \Log::info('Month: ' . $request->month);
        \Log::info('Note: ' . $request->note);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'month' => 'required',
            'note' => 'nullable|string',
        ]);
        // Enable query logging
        \DB::enableQueryLog();

        // Get the orders first to see what we're updating
        $orders = MonthlyOrder::where('product_id', $request->product_id)
            ->where('month', $request->month)
            ->get();

        \Log::info('Found orders: ' . $orders->count());

        // Update admin_notes for all orders of this product in this month
        $affected = MonthlyOrder::where('product_id', $request->product_id)
            ->where('month', $request->month)
            ->get();

        \Log::info('Found orders: ' . $orders->count());

        // Update each order individually to ensure it works
        $affected = 0;
        foreach ($orders as $order) {
            /** @var \App\Models\MonthlyOrder $order */

            \Log::info("Updating order ID: {$order->id}, current note: '{$order->notes}'");
            $order->notes = $request->note;
            $order->save();
            $affected++;
            \Log::info("After save, note is: '{$order->notes}'");
        }

        // Log the queries
        $queries = \DB::getQueryLog();
        \Log::info('SQL Queries executed:');
        foreach ($queries as $query) {
            \Log::info(json_encode($query));
        }

        \Log::info('Total rows updated: ' . $affected);

        return response()->json(['success' => true, 'affected' => $affected]);
    }
}

