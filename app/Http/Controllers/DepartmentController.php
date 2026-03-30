<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\MonthlyOrder;
use Illuminate\Support\Facades\Auth;
use App\Services\TimeService;
use Carbon\Carbon;

class DepartmentController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu của khoa
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        // Lấy tháng hiện tại hoặc tháng được chọn (Tự động chuyển tháng từ ngày 26)
        $selectedMonth = $request->input('month', TimeService::getCurrentCycleMonth());

        // Kiểm tra tháng được chọn phải là tháng hiện tại
        try {
            $selectedDate = \Carbon\Carbon::createFromFormat('m/Y', $selectedMonth)->startOfMonth();
            $currentDate = now()->startOfMonth();

            if (!$selectedDate->equalTo($currentDate) && !$selectedDate->equalTo($currentDate->copy()->addMonth())) {
                $errorMessage = $selectedDate->greaterThan($currentDate)
                    ? 'Không thể tạo yêu cầu cho tháng tương lai quá xa. Chỉ có thể tạo yêu cầu cho chu kỳ hiện tại.'
                    : 'Không thể tạo yêu cầu cho tháng quá khứ. Chỉ có thể tạo yêu cầu cho chu kỳ hiện tại.';

                return redirect()->route('department.index', ['month' => TimeService::getCurrentCycleMonth()])
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            // Nếu định dạng tháng không hợp lệ, chuyển về tháng hiện tại của chu kỳ
            return redirect()->route('department.index', ['month' => TimeService::getCurrentCycleMonth()]);
        }

        // Lấy tất cả categories và products
        $categories = Category::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('display_order')
            ->get();


        // Load existing orders for this month to pre-fill quantities
        $monthlyOrders = MonthlyOrder::where('department_id', $department->id)
            ->where('month', $selectedMonth)
            ->get()
            ->keyBy('product_id'); // keyed by product_id for easy lookup

        $canEdit = !TimeService::isPastDeadline($selectedMonth);
        $earliestMonth = TimeService::getEarliestMonth($department->id);

        return view('department.index', compact(
            'department',
            'categories',
            'products',
            'monthlyOrders',
            'selectedMonth',
            'canEdit',
            'earliestMonth'
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
            'orders.*.quantity' => 'required|integer|min:0',
            'orders.*.notes' => 'nullable|string|max:500',
        ]);

        // Kiểm tra tháng được chọn phải là tháng hiện tại
        try {
            $selectedDate = \Carbon\Carbon::createFromFormat('m/Y', $validated['month'])->startOfMonth();
            $currentDate = now()->startOfMonth();

            if (!$selectedDate->equalTo($currentDate) && !$selectedDate->equalTo($currentDate->copy()->addMonth())) {
                $errorMessage = $selectedDate->greaterThan($currentDate)
                    ? 'Không thể tạo yêu cầu cho tháng tương lai quá xa. Chỉ có thể tạo yêu cầu cho chu kỳ hiện tại.'
                    : 'Không thể tạo yêu cầu cho tháng quá khứ. Chỉ có thể tạo yêu cầu cho chu kỳ hiện tại.';

                return redirect()->back()->withErrors([
                    'month' => $errorMessage
                ])->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'month' => 'Định dạng tháng không hợp lệ.'
            ])->withInput();
        }

        // Hạn chót cho tháng đang yêu cầu (Sử dụng TimeService)
        $canEdit = !TimeService::isPastDeadline($validated['month']);

        // Nếu đã quá hạn chót, không cho phép lưu bất kỳ thay đổi nào (kể cả tạo mới)
        if (!$canEdit) {
            return redirect()->back()->with('error', 'Đã quá hạn chót (ngày 25) để gửi hoặc chỉnh sửa yêu cầu cho tháng ' . $validated['month'] . '.');
        }

        // Debug: Write to log file to see what's being received
        \Log::info('=== NOTES DEBUG ===', ['validated_orders' => $validated['orders']]);

        foreach ($validated['orders'] as $order) {
            // Lưu nếu có số lượng > 0 hoặc có ghi chú
            $hasNotes = !empty($order['notes']);
            $hasQuantity = $order['quantity'] > 0;

            if ($hasQuantity || $hasNotes) {
                MonthlyOrder::updateOrCreate(
                    [
                        'department_id' => $department->id,
                        'product_id' => $order['product_id'],
                        'month' => $validated['month'],
                    ],
                    [
                        'quantity' => $order['quantity'],
                        'notes' => $order['notes'] ?? null,
                    ]
                );
            } else {
                // Xóa order nếu không có số lượng và không có ghi chú
                MonthlyOrder::where([
                    'department_id' => $department->id,
                    'product_id' => $order['product_id'],
                    'month' => $validated['month'],
                ])->delete();
            }
        }

        return redirect()->back()->with('success', 'Đã lưu yêu cầu thành công!');
    }

    /**
     * Hiển thị lịch sử yêu cầu
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        $selectedMonth = $request->input('month', TimeService::getCurrentCycleMonth());

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
            ->sum(function ($order) {
                return $order->quantity * $order->product->price;
            });

        $canEdit = !TimeService::isPastDeadline($selectedMonth);
        $earliestMonth = TimeService::getEarliestMonth($department->id);
        $latestMonth = TimeService::getCurrentCycleMonth();

        return view('department.history', compact(
            'department',
            'orders',
            'selectedMonth',
            'totalAmount',
            'canEdit',
            'earliestMonth',
            'latestMonth'
        ));
    }

    /**
     * Hiển thị print view cho lịch sử yêu cầu
     */
    public function printHistory(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        // Lấy tháng được chọn hoặc tháng hiện tại của chu kỳ
        $selectedMonth = $request->input('month', TimeService::getCurrentCycleMonth());

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

    /**
     * Cập nhật số lượng qua AJAX
     */
    public function updateQuantity(Request $request, $id)
    {
        $order = MonthlyOrder::findOrFail($id);

        // Kiểm tra quyền
        if ($order->department_id !== Auth::user()->department_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa yêu cầu này.'
            ], 403);
        }

        // Kiểm tra hạn chót (Sử dụng TimeService để tránh gian lận giờ máy tính)
        if (TimeService::isPastDeadline($order->month)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã quá hạn chỉnh sửa yêu cầu của tháng ' . $order->month . ' (Sau 23:59:59 ngày 25 hàng tháng).'
            ], 403);
        }

        // Validate
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        // Cập nhật số lượng
        $order->quantity = $validated['quantity'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật số lượng thành công!',
            'data' => [
                'quantity' => $order->quantity,
                'total' => $order->quantity * $order->product->price
            ]
        ]);
    }

    /**
     * Cập nhật ghi chú qua AJAX
     */
    public function updateNotes(Request $request, $id)
    {
        $order = MonthlyOrder::findOrFail($id);

        // Kiểm tra quyền
        if ($order->department_id !== Auth::user()->department_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa yêu cầu này.'
            ], 403);
        }

        // Kiểm tra hạn chót
        if (TimeService::isPastDeadline($order->month)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã quá hạn chỉnh sửa ghi chú của tháng ' . $order->month . ' (Sau 23:59:59 ngày 25 hàng tháng).'
            ], 403);
        }

        // Validate
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Cập nhật ghi chú
        $order->notes = $validated['notes'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật ghi chú thành công!',
            'data' => [
                'notes' => $order->notes
            ]
        ]);
    }

    /**
     * Xóa yêu cầu qua AJAX
     */
    public function deleteOrder($id)
    {
        $order = MonthlyOrder::findOrFail($id);

        // Kiểm tra quyền sở hữu
        if ($order->department_id !== Auth::user()->department_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa yêu cầu này.'
            ], 403);
        }

        // Kiểm tra hạn chót
        if (TimeService::isPastDeadline($order->month)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã quá hạn xóa yêu cầu của tháng ' . $order->month . ' (Sau 23:59:59 ngày 25 hàng tháng).'
            ], 403);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa yêu cầu thành công!'
        ]);
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
