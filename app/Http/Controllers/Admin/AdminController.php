<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\AggregationBatch;
use App\Models\AggregationItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pendingRequests = PurchaseRequest::where('status', 'SUBMITTED')->count();
        $todayOrders = PurchaseOrder::whereDate('created_at', today())->count();
        $totalOrders = PurchaseOrder::count();

        return view('admin.dashboard', compact('pendingRequests', 'todayOrders', 'totalOrders'));
    }

    public function indexRequests()
    {
        $requests = PurchaseRequest::with(['department', 'requester', 'items.product.category.supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.requests.index', compact('requests'));
    }

    public function approveRequest($id)
    {
        try {
            DB::beginTransaction();
            $pr = PurchaseRequest::with(['items.product.category.supplier', 'department'])->findOrFail($id);

            if ($pr->status !== 'SUBMITTED') {
                throw new \Exception('Phiếu này đã được xử lý hoặc không hợp lệ.');
            }

            // Create POs for this single request (Split by Supplier)
            $itemsBySupplier = $pr->items->groupBy(function ($item) {
                return $item->product->supplier_id ?? 'NSA';
            });

            foreach ($itemsBySupplier as $supplierId => $items) {
                if ($supplierId === 'NSA')
                    continue;

                $year = now()->year;
                $month = now()->format('m');
                $deptCode = $pr->department->department_code;

                // Find last PO for this dept to determine sequence
                $lastPO = PurchaseOrder::where('department_id', $pr->department_id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $seq = 1;
                if ($lastPO && preg_match('/_(\d+)$/', $lastPO->order_code, $matches)) {
                    $seq = intval($matches[1]) + 1;
                }
                $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
                $poCode = "PO_{$year}_{$month}_{$deptCode}_{$seqStr}";

                $po = PurchaseOrder::create([
                    'order_code' => $poCode,
                    'supplier_id' => $supplierId,
                    'department_id' => $pr->department_id,
                    'status' => 'APPROVED',
                    'order_date' => now(),
                    'created_by' => Auth::id(),
                ]);

                $total = 0;
                foreach ($items as $item) {
                    $lineTotal = $item->quantity_requested * $item->product->unit_price;
                    $total += $lineTotal;

                    // Update item status
                    $item->update(['decision_status' => 'APPROVED']);
                }
                $po->update(['total_amount' => $total]);
            }

            $pr->update(['status' => 'APPROVED']);

            DB::commit();
            return redirect()->route('admin.orders.index')->with('success', 'Đã duyệt yêu cầu và tạo đơn hàng (PO) thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function indexAggregation()
    {
        $items = PurchaseRequestItem::whereHas('request', function ($query) {
            $query->where('status', 'SUBMITTED');
        })->with(['product.category.supplier', 'request.department'])->get();

        $aggregated = $items->groupBy('product_id')->map(function ($group) {
            return [
                'product' => $group->first()->product,
                'total_quantity' => $group->sum('quantity_requested'),
                'items' => $group, // Keep items to show details
                'supplier' => $group->first()->product->supplier,
            ];
        });

        return view('admin.aggregation.index', compact('aggregated'));
    }

    public function processAggregation(Request $request)
    {
        try {
            DB::beginTransaction();

            $items = PurchaseRequestItem::whereHas('request', function ($query) {
                $query->where('status', 'SUBMITTED');
            })->with(['product.category.supplier', 'request.department']) // Updated eager loading
                ->where('decision_status', '!=', 'REJECTED') // Skip rejected
                ->get();

            if ($items->isEmpty()) {
                throw new \Exception('Không có yêu cầu nào để tổng hợp.');
            }

            // VALIDATION: Check for Missing Suppliers
            $missingSupplierProducts = [];
            foreach ($items as $item) {
                // Check supplier via Category
                if (!$item->product || !$item->product->category || !$item->product->category->supplier_id) {
                    $missingSupplierProducts[] = $item->product->product_name ?? 'Item #' . $item->request_item_id;
                }
            }

            if (!empty($missingSupplierProducts)) {
                $missingSupplierProducts = array_unique($missingSupplierProducts);
                $names = implode(', ', array_slice($missingSupplierProducts, 0, 3));
                if (count($missingSupplierProducts) > 3)
                    $names .= '...';
                throw new \Exception('Các sản phẩm sau chưa được gán Nhà Cung Cấp (qua Danh mục): ' . $names . '. Vui lòng kiểm tra lại cấu hình Danh mục sản phẩm.');
            }

            // Group by Supplier -> Department
            $bySupplier = $items->groupBy(function ($item) {
                return $item->product->category->supplier_id; // Access via Category
            });

            foreach ($bySupplier as $supplierId => $supplierItems) {
                if ($supplierId === 'NSA')
                    continue;

                $byDept = $supplierItems->groupBy(function ($item) {
                    return $item->request->department_id;
                });

                foreach ($byDept as $deptId => $deptItems) {
                    $department = $deptItems->first()->request->department;

                    $year = now()->year;
                    $month = now()->format('m');
                    $deptCode = $department->department_code;

                    // Sequence logic: Reset per Department
                    $lastPO = PurchaseOrder::where('department_id', $deptId)
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $seq = 1;
                    if ($lastPO && preg_match('/_(\d+)$/', $lastPO->order_code, $matches)) {
                        $seq = intval($matches[1]) + 1;
                    }
                    $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
                    $poCode = "PO_{$year}_{$month}_{$deptCode}_{$seqStr}";

                    $po = PurchaseOrder::create([
                        'order_code' => $poCode,
                        'supplier_id' => $supplierId,
                        'department_id' => $deptId,
                        'status' => 'APPROVED',
                        'order_date' => now(),
                        'created_by' => Auth::id(),
                    ]);

                    $byProduct = $deptItems->groupBy('product_id');
                    $poTotal = 0;

                    foreach ($byProduct as $prodId => $prodItems) {
                        $qty = $prodItems->sum('quantity_requested');
                        $product = $prodItems->first()->product;
                        $lineTotal = $qty * $product->unit_price;

                        $poTotal += $lineTotal;

                        // Update items status
                        foreach ($prodItems as $item) {
                            $item->update(['decision_status' => 'APPROVED']);
                        }
                    }
                    $po->update(['total_amount' => $poTotal]);
                }
            }

            $requestIds = $items->pluck('purchase_request_id')->unique();
            PurchaseRequest::whereIn('purchase_request_id', $requestIds)->update(['status' => 'APPROVED']);

            DB::commit();
            return redirect()->route('admin.orders.index')->with('success', 'Đã tổng hợp và tạo các đơn hàng (PO) thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function indexOrders()
    {
        $orders = PurchaseOrder::with(['supplier', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }
    public function approveSummaryVotes()
    {
        // 1. Get raw items for Detail View
        $items = PurchaseRequestItem::whereHas('request', function ($query) {
            $query->whereIn('status', ['SUBMITTED', 'APPROVED']);
        })->with(['product.category.supplier', 'request.department'])
            ->get();

        $groupedByDept = $items->groupBy(function ($item) {
            return $item->request->department_id;
        });

        $departments = \App\Models\Department::orderBy('department_code')->get();

        // 2. Sync with Aggregation Logic (DB)
        // Find or Create DRAFT Batch for current month
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $currentBatch = AggregationBatch::firstOrCreate(
            [
                'batch_month' => $currentMonth,
                'batch_year' => $currentYear,
                'status' => 'ISSUED'
            ],
            [
                'batch_code' => "AGG-{$currentYear}-{$currentMonth}",
                'created_by' => Auth::id()
            ]
        );

        // Group valid items by Product to sync quantities
        $aggregatedData = $items->groupBy('product_id');

        // Sync Logic: Update/Create Items
        foreach ($aggregatedData as $productId => $prodItems) {
            $totalQty = $prodItems->where('decision_status', '!=', 'REJECTED')->sum('quantity_requested');

            if ($totalQty == 0)
                continue; // Skip if all rejected
            $product = $prodItems->first()->product;

            AggregationItem::updateOrCreate(
                [
                    'aggregation_batch_id' => $currentBatch->aggregation_batch_id,
                    'product_id' => $productId,
                ],
                [
                    'supplier_id' => $product->supplier_id,
                    'total_approved' => $totalQty, // Default to requested
                ]
            );
        }

        // Cleanup: Remove AggregationItems that are NO LONGER in the pending list? 
        // For simplicity/performance, we might skip deletion or do it if strictness required.
        // Let's assume strictly syncing:
        // Get all product IDs in current pending list
        $activeProductIds = $aggregatedData->keys()->toArray();
        AggregationItem::where('aggregation_batch_id', $currentBatch->aggregation_batch_id)
            ->whereNotIn('product_id', $activeProductIds)
            ->delete();

        // 3. Fetch from DB for View (with Note)
        $aggregationItems = AggregationItem::where('aggregation_batch_id', $currentBatch->aggregation_batch_id)
            ->with(['product.category.supplier'])
            ->get();

        $aggregatedBySupplier = $aggregationItems->groupBy(function ($item) {
            return $item->product->supplier->supplier_name ?? 'Chưa gán NCC';
        });

        // 4. Pivot Data for "BẢNG TỔNG"
        $issuedPOsExist = PurchaseOrder::where('status', 'ISSUED')->exists();
        $pivotData = collect();
        $allDepartments = \App\Models\Department::orderBy('department_code')->get();

        if ($currentBatch) {
            $productIds = $aggregationItems->pluck('product_id')->unique()->toArray();

            // Calculate Totals per Department
            $deptProductCounts = collect(); // For top red row (Count of items)
            $deptQtyTotals = collect();     // For bottom total row (Sum of quantities)

            $allRequestItems = PurchaseRequestItem::whereIn('product_id', $productIds)
                ->where('decision_status', '!=', 'REJECTED')
                ->with('request') // Eager load request to access department_id
                ->get();

            foreach ($allDepartments as $dept) {
                $deptItems = $allRequestItems->where('request.department_id', $dept->department_id);
                $deptProductCounts[$dept->department_id] = $deptItems->unique('product_id')->count();
                $deptQtyTotals[$dept->department_id] = $deptItems->sum('quantity_requested');
            }

            // Paginate product IDs manually
            $page = request()->get('page', 1);
            $perPage = 15;
            $paginatedProductIds = collect($productIds)->forPage($page, $perPage);

            $requestItems = PurchaseRequestItem::whereIn('product_id', $paginatedProductIds)
                ->where('decision_status', '!=', 'REJECTED')
                ->with(['product', 'request.department'])
                ->get();

            // Build pivot: products × departments
            $pivotData = $requestItems->groupBy('product_id')->map(function ($items) use ($allDepartments) {
                $row = [
                    'product' => $items->first()->product,
                    'departments' => []
                ];

                foreach ($allDepartments as $dept) {
                    $deptItems = $items->filter(function ($item) use ($dept) {
                        return $item->request && $item->request->department_id == $dept->department_id;
                    });
                    $qty = $deptItems->sum('quantity_requested');
                    $row['departments'][$dept->department_id] = $qty > 0 ? $qty : null;
                }

                $row['total'] = $items->sum('quantity_requested');
                return $row;
            });

            // Create a manual LengthAwarePaginator for the view
            $pivotPagination = new \Illuminate\Pagination\LengthAwarePaginator(
                $pivotData,
                count($productIds),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return view('admin.approve_summary_votes', compact(
            'groupedByDept',
            'departments',
            'items',
            'aggregatedBySupplier',
            'issuedPOsExist',
            'pivotData',
            'allDepartments',
            'currentBatch',
            'pivotPagination',
            'deptProductCounts',
            'deptQtyTotals'
        ));
    }

    public function approveDepartment($deptId)
    {
        try {
            DB::beginTransaction();

            $items = PurchaseRequestItem::whereHas('request', function ($query) use ($deptId) {
                $query->where('status', 'SUBMITTED')
                    ->where('department_id', $deptId);
            })->with(['product.category.supplier', 'request.department'])
                ->where('decision_status', '!=', 'REJECTED')
                ->get();

            if ($items->isEmpty()) {
                throw new \Exception('Không có yêu cầu nào hợp lệ của khoa này để duyệt.');
            }

            // VALIDATION: Check for Missing Suppliers
            foreach ($items as $item) {
                if (!$item->product || !$item->product->category || !$item->product->category->supplier_id) {
                    throw new \Exception('Sản phẩm "' . ($item->product->product_name ?? 'Unknown') . '" chưa có nhà cung cấp (qua danh mục).');
                }
            }

            // Group by Supplier
            $bySupplier = $items->groupBy(function ($item) {
                return $item->product->category->supplier_id;
            });

            foreach ($bySupplier as $supplierId => $supplierItems) {
                if ($supplierId === 'NSA')
                    continue;

                $department = $supplierItems->first()->request->department;
                $year = now()->year;
                $month = now()->format('m');
                $deptCode = $department->department_code;

                // Create PO
                $lastPO = PurchaseOrder::where('department_id', $deptId)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $seq = 1;
                if ($lastPO && preg_match('/_(\d+)$/', $lastPO->order_code, $matches)) {
                    $seq = intval($matches[1]) + 1;
                }
                $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
                $poCode = "PO_{$year}_{$month}_{$deptCode}_{$seqStr}";

                $po = PurchaseOrder::create([
                    'order_code' => $poCode,
                    'supplier_id' => $supplierId,
                    'department_id' => $deptId,
                    'status' => 'APPROVED',
                    'order_date' => now(),
                    'created_by' => Auth::id(),
                ]);

                $poTotal = 0;
                $byProduct = $supplierItems->groupBy('product_id');

                foreach ($byProduct as $prodId => $prodItems) {
                    $qty = $prodItems->sum('quantity_requested');
                    $product = $prodItems->first()->product;
                    $lineTotal = $qty * $product->unit_price;
                    $poTotal += $lineTotal;

                    foreach ($prodItems as $item) {
                        $item->update(['decision_status' => 'APPROVED']);
                    }
                }
                $po->update(['total_amount' => $poTotal]);
            }

            // Update Request Status
            $requestIds = $items->pluck('purchase_request_id')->unique();
            PurchaseRequest::whereIn('purchase_request_id', $requestIds)->update(['status' => 'APPROVED']);

            DB::commit();
            return redirect()->back()->with('success', 'Đã duyệt toàn bộ khoa và tạo PO thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function approveRequestItem($id)
    {
        try {
            DB::beginTransaction();

            \Log::info("Approve request received for item ID: " . $id);
            $item = PurchaseRequestItem::findOrFail($id);
            \Log::info("Item found: " . $item->request_item_id);

            $item->update(['decision_status' => 'APPROVED']);
            \Log::info("Item updated successfully");

            // Check parent request status
            $request = $item->request;
            $allItems = $request->items;

            $approvedCount = $allItems->where('decision_status', 'APPROVED')->count();
            $rejectedCount = $allItems->where('decision_status', 'REJECTED')->count();
            $totalCount = $allItems->count();

            // Update parent request status based on items
            if ($approvedCount > 0 && $request->status === 'SUBMITTED') {
                // At least one approved → Request becomes APPROVED
                $request->update(['status' => 'APPROVED']);
                \Log::info("Request {$request->purchase_request_id} updated to APPROVED");

                // Create PO for approved items only
                $this->createPOForRequest($request);
            } elseif ($rejectedCount === $totalCount) {
                // All rejected → Request becomes REJECTED
                $request->update(['status' => 'REJECTED']);
                \Log::info("Request {$request->purchase_request_id} updated to REJECTED");
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đã duyệt thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error approving item: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function rejectRequestItem($id)
    {
        try {
            DB::beginTransaction();

            $item = PurchaseRequestItem::findOrFail($id);
            $item->update(['decision_status' => 'REJECTED']);

            // Check parent request status
            $request = $item->request;
            $allItems = $request->items;

            $rejectedCount = $allItems->where('decision_status', 'REJECTED')->count();
            $totalCount = $allItems->count();

            // If all items are rejected, mark request as REJECTED
            if ($rejectedCount === $totalCount) {
                $request->update(['status' => 'REJECTED']);
                \Log::info("Request {$request->purchase_request_id} updated to REJECTED (all items rejected)");
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function createPOForRequest($request)
    {
        // Get only APPROVED items
        $approvedItems = $request->items()->where('decision_status', 'APPROVED')->get();

        if ($approvedItems->isEmpty()) {
            return;
        }

        // Group by supplier (via category)
        $bySupplier = $approvedItems->groupBy(function ($item) {
            return $item->product->category->supplier_id ?? 'NSA';
        });

        foreach ($bySupplier as $supplierId => $items) {
            if ($supplierId === 'NSA')
                continue;

            $year = now()->year;
            $month = now()->format('m');
            $deptCode = $request->department->department_code;

            // Generate PO code
            $lastPO = PurchaseOrder::where('department_id', $request->department_id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('created_at', 'desc')
                ->first();

            $seq = 1;
            if ($lastPO && preg_match('/_(\d+)$/', $lastPO->order_code, $matches)) {
                $seq = intval($matches[1]) + 1;
            }
            $seqStr = str_pad($seq, 3, '0', STR_PAD_LEFT);
            $poCode = "PO_{$year}_{$month}_{$deptCode}_{$seqStr}";

            // Create PO
            $po = PurchaseOrder::create([
                'order_code' => $poCode,
                'supplier_id' => $supplierId,
                'department_id' => $request->department_id,
                'status' => 'APPROVED',
                'order_date' => now(),
                'created_by' => Auth::id(),
            ]);

            $total = 0;
            foreach ($items as $item) {
                $unitPrice = $item->product->unit_price ?? 0;
                $lineTotal = $item->quantity_requested * $unitPrice;
                $total += $lineTotal;

                // Create PO item
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->purchase_order_id,
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity_requested,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'aggregation_item_id' => null, // Linking to null as it's from request directly
                ]);
            }

            $po->update(['total_amount' => $total]);
            \Log::info("Created PO {$poCode} for request {$request->request_code}");
        }
    }

    public function updateAggregationItemNote(Request $request, $id)
    {
        try {
            $item = AggregationItem::findOrFail($id);
            $item->note = $request->input('note');
            $item->save();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showOrder($id)
    {
        $order = PurchaseOrder::with(['items.product', 'department', 'supplier'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = PurchaseOrder::findOrFail($id);
        if ($request->has('status')) {
            $order->update(['status' => $request->status]);
        }
        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }
}
