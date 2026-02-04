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
        $categoriesQuery = Category::where('is_active', true)->orderBy('display_order');
        
        // Nếu có filter category, chỉ lấy category đó
        if ($selectedCategory) {
            $categories = $categoriesQuery->where('id', $selectedCategory)->get();
        } else {
            $categories = $categoriesQuery->get();
        }

        // Lấy tất cả products với orders của tháng
        $productsQuery = Product::where('is_active', true)
            ->with(['category', 'monthlyOrders' => function($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth)
                    ->with('department');
            }])
            ->orderBy('category_id')
            ->orderBy('display_order');
        
        // Filter theo category nếu có
        if ($selectedCategory) {
            $productsQuery->where('category_id', $selectedCategory);
        }
        
        $products = $productsQuery->get()->groupBy('category_id');

        // Lấy tất cả categories cho dropdown (không filter)
        $allCategories = Category::where('is_active', true)->orderBy('display_order')->get();

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
}
