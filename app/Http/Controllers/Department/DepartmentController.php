<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user->department) {
            return redirect()->route('login')->with('error', 'User has no department.');
        }

        $requests = PurchaseRequest::where('department_id', $user->department_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $pendingCount = PurchaseRequest::where('department_id', $user->department_id)
            ->where('status', 'SUBMITTED')
            ->count();

        $approvedCount = PurchaseRequest::where('department_id', $user->department_id)
            ->where('status', 'APPROVED')
            ->count();

        return view('department.dashboard', compact('requests', 'pendingCount', 'approvedCount'));
    }

    public function createrequest()
    {
        $categories = \App\Models\Category::all();
        return view('department.request', compact('categories'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        $categoryId = $request->get('category_id');

        $productsQuery = Product::query()->select('product_id', 'product_name', 'product_code', 'unit', 'unit_price', 'category_id');

        if ($query) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('product_name', 'LIKE', "%{$query}%")
                    ->orWhere('product_code', 'LIKE', "%{$query}%");
            });
        }

        if ($categoryId && $categoryId !== 'all') {
            $productsQuery->where('category_id', $categoryId);
        }

        $products = $productsQuery->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $department = $user->department;

            if (!$department) {
                throw new \Exception('Người dùng không thuộc khoa phòng nào.');
            }

            // Code Format: REQ_<Y>_<M>_<Dept>_<Seq>
            $year = now()->year;
            $month = now()->format('m');
            $deptCode = $department->department_code;

            // Find last request for this dept, this month/year to determine sequence
            $lastRequest = PurchaseRequest::where('department_id', $department->department_id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('created_at', 'desc')
                ->first();

            $seq = 1;
            if ($lastRequest && preg_match('/_(\d+)$/', $lastRequest->request_code, $matches)) {
                $seq = intval($matches[1]) + 1;
            }

            $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
            $requestCode = "REQ_{$year}_{$month}_{$deptCode}_{$seqStr}";

            $pr = PurchaseRequest::create([
                'request_code' => $requestCode,
                'department_id' => $department->department_id,
                'requester_id' => $user->user_id,
                'request_date' => now(),
                'status' => 'SUBMITTED',
                'created_by' => $user->user_id,
            ]);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->purchase_request_id,
                    'product_id' => $product->product_id,
                    'quantity_requested' => $item['quantity'],
                    'decision_status' => 'PENDING',
                    'created_by' => $user->user_id,
                ]);
                $totalAmount += ($product->unit_price * $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu thành công: ' . $requestCode,
                'request_code' => $requestCode,
                'redirect' => route('department.dashboard')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listRequests()
    {
        $user = Auth::user();
        $requests = PurchaseRequest::where('department_id', $user->department_id)
            ->with(['requester', 'items', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $allRequests = PurchaseRequest::where('department_id', $user->department_id)->get();
        $statistics = [
            'total' => $allRequests->count(),
            'pending' => $allRequests->where('status', 'SUBMITTED')->count(),
            'approved' => $allRequests->where('status', 'APPROVED')->count(),
            'rejected' => $allRequests->where('status', 'REJECTED')->count(),
        ];

        return view('department.list_request', compact('requests', 'statistics'));
    }

    public function getRequestDetail($id)
    {
        try {
            $user = Auth::user();
            $request = PurchaseRequest::where('purchase_request_id', $id)
                ->where('department_id', $user->department_id)
                ->with(['requester', 'items.product', 'department'])
                ->firstOrFail();

            $statusLabels = [
                'draft' => 'Nháp',
                'SUBMITTED' => 'Chờ duyệt',
                'pending' => 'Chờ duyệt',
                'APPROVED' => 'Đã duyệt',
                'REJECTED' => 'Từ chối',
                'ISSUED' => 'Đã phát hành',
            ];

            $items = $request->items->map(function ($item) {
                return [
                    'product_name' => $item->product->product_name ?? 'N/A',
                    'sku' => $item->product->sku ?? 'N/A',
                    'unit' => $item->product->unit ?? 'N/A',
                    'quantity_requested' => $item->quantity_requested,
                    'unit_price' => $item->product->unit_price ?? 0,
                    'total_price' => $item->quantity_requested * ($item->product->unit_price ?? 0),
                ];
            });

            $totalAmount = $items->sum('total_price');

            return response()->json([
                'success' => true,
                'data' => [
                    'request_code' => $request->request_code,
                    'request_date' => $request->created_at->format('d/m/Y H:i'),
                    'status' => $request->status,
                    'status_label' => $statusLabels[$request->status] ?? 'N/A',
                    'requester_name' => $request->requester->full_name ?? 'N/A',
                    'department_name' => $request->department->department_name ?? 'N/A',
                    'note' => $request->note ?? '',
                    'items' => $items,
                    'total_amount' => $totalAmount,
                    'items_count' => $items->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hoặc bạn không có quyền xem.'
            ], 404);
        }
    }
}
