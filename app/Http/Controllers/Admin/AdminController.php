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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
            // Calculate Totals per Department
            $deptTotalRequested = collect(); // For top red row (All requested, even rejected)
            $deptQtyTotals = collect();      // For bottom total row (Only non-rejected)

            // All items for the products present in this batch
            $allRequestItemsForProducts = PurchaseRequestItem::whereIn('product_id', $productIds)
                ->with('request')
                ->get();

            foreach ($allDepartments as $dept) {
                $deptItems = $allRequestItemsForProducts->where('request.department_id', $dept->department_id);

                // Top row: All requested quantities (including rejected)
                $deptTotalRequested[$dept->department_id] = $deptItems->sum('quantity_requested');

                // Bottom row: Only non-rejected quantities
                $deptQtyTotals[$dept->department_id] = $deptItems->where('decision_status', '!=', 'REJECTED')->sum('quantity_requested');
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
            'deptTotalRequested',
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

    public function printAggregation()
    {
        // Fetch aggregation data
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $currentBatch = AggregationBatch::where('batch_month', $currentMonth)
            ->where('batch_year', $currentYear)
            ->where('status', 'ISSUED')
            ->first();

        if (!$currentBatch) {
            return back()->with('error', 'Không có dữ liệu tổng hợp để in.');
        }

        $aggregationItems = AggregationItem::where('aggregation_batch_id', $currentBatch->aggregation_batch_id)
            ->with(['product.category.supplier'])
            ->get();

        $aggregatedBySupplier = $aggregationItems->groupBy(function ($item) {
            return $item->product->supplier->supplier_name ?? 'Chưa gán NCC';
        });

        return view('admin.print_aggregation', compact('aggregatedBySupplier', 'currentBatch'));
    }

    public function exportAggregationExcel()
    {
        // Fetch aggregation data
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $currentBatch = AggregationBatch::where('batch_month', $currentMonth)
            ->where('batch_year', $currentYear)
            ->where('status', 'ISSUED')
            ->first();

        if (!$currentBatch) {
            return back()->with('error', 'Không có dữ liệu tổng hợp để xuất.');
        }

        // Get aggregation items
        $aggregationItems = AggregationItem::where('aggregation_batch_id', $currentBatch->aggregation_batch_id)
            ->with(['product.category.supplier'])
            ->get();

        // Get all approved request items to map products to departments
        $requestItems = PurchaseRequestItem::where('decision_status', 'APPROVED')
            ->with(['request.department'])
            ->get();

        // Create a map of product_id => department_name
        $productDepartmentMap = [];
        foreach ($requestItems as $item) {
            if ($item->request && $item->request->department) {
                $productId = $item->product_id;
                $deptName = $item->request->department->department_name;
                
                // Store all departments that requested this product
                if (!isset($productDepartmentMap[$productId])) {
                    $productDepartmentMap[$productId] = [];
                }
                if (!in_array($deptName, $productDepartmentMap[$productId])) {
                    $productDepartmentMap[$productId][] = $deptName;
                }
            }
        }

        // Group aggregation items by department
        $groupedByDepartment = [];
        foreach ($aggregationItems as $aggItem) {
            $productId = $aggItem->product_id;
            $departments = $productDepartmentMap[$productId] ?? ['Chưa gán khoa'];
            
            // Add this item to each department that requested it
            foreach ($departments as $deptName) {
                if (!isset($groupedByDepartment[$deptName])) {
                    $groupedByDepartment[$deptName] = collect();
                }
                $groupedByDepartment[$deptName]->push($aggItem);
            }
        }

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        $sheetIndex = 0;
        foreach ($groupedByDepartment as $departmentName => $departmentItems) {
            // Create new sheet for each department
            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $departmentName);
            $spreadsheet->addSheet($sheet, $sheetIndex);
            
            // Set page setup
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

            // Header - Row 1
            $sheet->setCellValue('A1', 'CTY CP BV ĐA KHOA TÂM TRÍ CAO LÃNH');
            $sheet->setCellValue('F1', 'Mẫu số 02-VT');
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->getStyle('F1')->getFont()->setBold(true);
            $sheet->getStyle('F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Row 2
            $sheet->setCellValue('A2', 'P. HỖ TRỢ DỊCH VỤ');
            $sheet->getStyle('A2')->getFont()->setBold(true);
            
            // Row 3-4 - Legal reference
            $sheet->mergeCells('A3:F3');
            $sheet->setCellValue('A3', '(Ban hành theo TT số 200/2014/TT-BTC');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
            
            $sheet->mergeCells('A4:F4');
            $sheet->setCellValue('A4', 'Ngày 22/12/2014 của Bộ trưởng BTC)');
            $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(10);

            // Title - Row 6
            $sheet->mergeCells('A6:F6');
            $sheet->setCellValue('A6', 'PHIẾU XUẤT KHO VÀ BIÊN BẢN BÀN GIAO NỘI BỘ');
            $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Date - Row 7
            $sheet->mergeCells('A7:F7');
            $sheet->setCellValue('A7', 'Ngày ' . now()->format('d/m/Y'));
            $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A7')->getFont()->setItalic(true);

            // Department section - Row 8
            $sheet->mergeCells('A8:F8');
            $sheet->setCellValue('A8', strtoupper($departmentName));
            $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table header - Row 10
            $currentRow = 10;
            $sheet->setCellValue("A{$currentRow}", 'STT');
            $sheet->setCellValue("B{$currentRow}", 'Tên hàng');
            $sheet->setCellValue("C{$currentRow}", 'ĐVT');
            $sheet->setCellValue("D{$currentRow}", 'Số Lượng');
            $sheet->setCellValue("E{$currentRow}", 'Đơn giá');
            $sheet->setCellValue("F{$currentRow}", 'Thành tiền');

            // Style table header
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9EAD3']
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            $currentRow++;

            // Group items by supplier within this department
            $groupedBySupplier = $departmentItems->groupBy(function ($item) {
                return $item->product->supplier->supplier_name ?? 'Chưa gán NCC';
            });

            $departmentTotal = 0;
            $globalStt = 1;

            foreach ($groupedBySupplier as $supplierName => $supplierItems) {
                // Supplier header row
                $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", strtoupper($supplierName));
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC']
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
                ]);

                $currentRow++;
                $supplierTotal = 0;

                // Group by product within supplier
                $groupedByProduct = $supplierItems->groupBy('product_id');
                foreach ($groupedByProduct as $prodId => $prods) {
                    $firstItem = $prods->first();
                    $prod = $firstItem->product;
                    // Use total_approved from AggregationItem (not quantity_approved from PurchaseRequestItem)
                    $totalQty = $prods->sum('total_approved');
                    $lineTotal = $totalQty * $prod->unit_price;
                    $supplierTotal += $lineTotal;
                    $departmentTotal += $lineTotal;

                    $sheet->setCellValue("A{$currentRow}", $globalStt++);
                    $sheet->setCellValue("B{$currentRow}", $prod->product_name);
                    $sheet->setCellValue("C{$currentRow}", $prod->unit);
                    $sheet->setCellValue("D{$currentRow}", $totalQty);
                    $sheet->setCellValue("E{$currentRow}", $prod->unit_price);
                    $sheet->setCellValue("F{$currentRow}", $lineTotal);

                    // Style data rows
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
                    ]);
                    
                    $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Number formatting
                    $sheet->getStyle("E{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');

                    $currentRow++;
                }

                // Supplier subtotal
                $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'Tổng cộng:');
                $sheet->setCellValue("F{$currentRow}", $supplierTotal);
                $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F3F3']
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');

                $currentRow++;
            }

            $currentRow++;

            // Department grand total
            $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'TỔNG GIÁ TRỊ ĐƠN HÀNG:');
            $sheet->setCellValue("F{$currentRow}", $departmentTotal);
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$currentRow}")->getFont()->getColor()->setRGB('0000FF');

            $currentRow += 2;

            // Note
            $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", '* Ghi chú: Hàng hóa được bàn giao đúng chất lượng và số lượng như trên.');
            $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setSize(10);

            $currentRow += 3;

            // Signature section
            $sheet->setCellValue("A{$currentRow}", 'NGƯỜI LẬP PHIẾU');
            $sheet->setCellValue("C{$currentRow}", 'THỦ KHO / KẾ TOÁN');
            $sheet->setCellValue("E{$currentRow}", 'GIÁM ĐỐC');
            
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");

            $currentRow++;
            $sheet->setCellValue("A{$currentRow}", '(Ký, họ tên)');
            $sheet->setCellValue("C{$currentRow}", '(Ký, họ tên)');
            $sheet->setCellValue("E{$currentRow}", '(Ký, họ tên)');
            
            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFont()->setItalic(true)->setSize(10);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");

            $currentRow += 4;
            $userName = Auth::user()->full_name ?? '';
            $sheet->setCellValue("A{$currentRow}", $userName);
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(40);
            $sheet->getColumnDimension('C')->setWidth(10);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);

            // Set row heights for better spacing
            $sheet->getRowDimension(10)->setRowHeight(25);

            $sheetIndex++;
        }

        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Phieu_Xuat_Kho_' . now()->format('dmY') . '.xlsx';
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
