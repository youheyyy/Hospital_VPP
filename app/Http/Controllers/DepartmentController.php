<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\MonthlyOrder;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu của khoa
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        // Lấy tháng hiện tại hoặc tháng được chọn
        $selectedMonth = $request->input('month', date('m/Y'));

        // Lấy tất cả categories và products
        $categories = Category::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        // Lấy các yêu cầu hiện tại của khoa trong tháng
        $monthlyOrders = MonthlyOrder::where('department_id', $department->id)
            ->where('month', $selectedMonth)
            ->with('product')
            ->get()
            ->keyBy('product_id');

        return view('department.index', compact(
            'department',
            'categories',
            'products',
            'monthlyOrders',
            'selectedMonth'
        ));
    }

    /**
     * Lưu hoặc cập nhật yêu cầu
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        $validated = $request->validate([
            'month' => 'required|string',
            'orders' => 'required|array',
            'orders.*.product_id' => 'required|exists:products,id',
            'orders.*.quantity' => 'required|numeric|min:0',
        ]);

        // Kiểm tra ngày hiện tại
        $currentDay = now()->day;
        $canEdit = $currentDay < 5;

        foreach ($validated['orders'] as $order) {
            if ($order['quantity'] > 0) {
                // Kiểm tra xem đã có order này chưa
                $existingOrder = MonthlyOrder::where([
                    'department_id' => $department->id,
                    'product_id' => $order['product_id'],
                    'month' => $validated['month'],
                ])->first();

                // Nếu sau ngày 5 và đã có order, không cho phép cập nhật
                if (!$canEdit && $existingOrder) {
                    continue; // Bỏ qua việc cập nhật order đã tồn tại
                }

                // Cho phép tạo mới hoặc cập nhật nếu trước ngày 5
                MonthlyOrder::updateOrCreate(
                    [
                        'department_id' => $department->id,
                        'product_id' => $order['product_id'],
                        'month' => $validated['month'],
                    ],
                    [
                        'quantity' => $order['quantity'],
                    ]
                );
            }
        }

        $message = $canEdit ? 'Đã lưu yêu cầu thành công!' : 'Đã lưu yêu cầu mới thành công! (Không thể chỉnh sửa yêu cầu cũ sau ngày 5)';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Hiển thị lịch sử yêu cầu
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        // Lấy tháng được chọn hoặc tháng hiện tại
        $selectedMonth = $request->input('month', date('m/Y'));

        // Lấy tất cả yêu cầu của khoa trong tháng
        $orders = MonthlyOrder::where('department_id', $department->id)
            ->where('month', $selectedMonth)
            ->with(['product.category'])
            ->get()
            ->groupBy('product.category.name');

        // Tính tổng
        $totalAmount = MonthlyOrder::where('department_id', $department->id)
            ->where('month', $selectedMonth)
            ->with('product')
            ->get()
            ->sum(function($order) {
                return $order->quantity * $order->product->price;
            });

        return view('department.history', compact(
            'department',
            'orders',
            'selectedMonth',
            'totalAmount'
        ));
    }

    /**
     * Xóa yêu cầu
     */
    public function destroy($id)
    {
        $order = MonthlyOrder::findOrFail($id);

        // Kiểm tra quyền
        if ($order->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        $order->delete();

        return redirect()->back()->with('success', 'Đã xóa yêu cầu!');
    }
}
