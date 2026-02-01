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
        $totalDepartments = \App\Models\Department::count();
        $totalOrders = PurchaseOrder::count();

        return view('admin.dashboard', compact('pendingRequests', 'totalDepartments', 'totalOrders'));
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
                        ->orderBy('purchase_order_id', 'desc') // Fix: Order by ID
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

                        // Create PO Item
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $po->purchase_order_id,
                            'product_id' => $prodId,
                            'quantity_ordered' => $qty,
                            'unit_price' => $product->unit_price,
                            'total_price' => $lineTotal,
                        ]);

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

    /* --- PRODUCT MANAGEMENT --- */

    public function indexProducts(Request $request)
    {
        $query = Product::with(['category.supplier']);

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('product_code', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);
        $categories = \App\Models\Category::all();

        return view('admin.product', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:200',
            'category_id' => 'required|exists:product_categories,category_id',
            'unit' => 'nullable|string|max:50',
            'unit_price' => 'nullable|numeric',
        ]);

        // Generate product code
        $category = \App\Models\Category::findOrFail($request->category_id);
        $prefix = $category->category_code ?: 'PROD';
        $lastProduct = Product::where('category_id', $request->category_id)->orderBy('product_id', 'desc')->first();
        $seq = 1;
        if ($lastProduct && preg_match('/(\d+)$/', $lastProduct->product_code, $matches)) {
            $seq = intval($matches[1]) + 1;
        }
        $code = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

        Product::create(array_merge($request->all(), [
            'product_code' => $code,
            'created_by' => Auth::id()
        ]));

        return redirect()->back()->with('success', 'Thêm sản phẩm thành công.');
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'product_name' => 'required|string|max:200',
            'category_id' => 'required|exists:product_categories,category_id',
            'unit' => 'nullable|string|max:50',
            'unit_price' => 'nullable|numeric',
        ]);

        $product->update($request->all());

        return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->back()->with('success', 'Xóa sản phẩm thành công.');
    }

    /* --- MANAGEMENT FUNCTIONALITY --- */

    public function indexManagement()
    {
        $categories = \App\Models\Category::with('supplier')->get();
        $suppliers = Supplier::all();
        $departments = \App\Models\Department::all();
        $users = \App\Models\User::with('department')->get();

        return view('admin.management', compact('categories', 'suppliers', 'departments', 'users'));
    }

    // Category CRUD
    public function storeCategory(Request $request)
    {
        $request->validate(['category_name' => 'required']);

        // Auto-generate code: First letters of each word
        $name = $request->category_name;
        $words = explode(' ', \Str::slug($name, ' '));
        $code = '';
        foreach ($words as $w) {
            $code .= mb_substr($w, 0, 1);
        }
        $code = strtoupper($code);

        // Ensure unique
        if (\App\Models\Category::where('category_code', $code)->exists()) {
            $code .= '_' . rand(1, 99);
        }

        $data = $request->all();
        $data['category_code'] = $code;

        \App\Models\Category::create($data);
        return redirect()->route('admin.management', ['tab' => 'categories'])->with('success', 'Thêm danh mục thành công (Mã: ' . $code . ').');
    }

    public function updateCategory(Request $request, $id)
    {
        $item = \App\Models\Category::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('admin.management', ['tab' => 'categories'])->with('success', 'Cập nhật danh mục thành công.');
    }

    public function destroyCategory($id)
    {
        \App\Models\Category::findOrFail($id)->delete();
        return redirect()->route('admin.management', ['tab' => 'categories'])->with('success', 'Xóa danh mục thành công.');
    }

    // Supplier CRUD
    public function storeSupplier(Request $request)
    {
        $request->validate(['supplier_name' => 'required']);

        // Auto-generate code: SUP + increment
        $lastSup = Supplier::orderBy('supplier_id', 'desc')->first();
        $seq = 1;
        if ($lastSup && preg_match('/SUP(\d+)/', $lastSup->supplier_code, $matches)) {
            $seq = intval($matches[1]) + 1;
        }
        $code = 'SUP' . str_pad($seq, 3, '0', STR_PAD_LEFT); // SUP001

        $data = $request->all();
        $data['supplier_code'] = $code;

        Supplier::create($data);
        return redirect()->route('admin.management', ['tab' => 'suppliers'])->with('success', 'Thêm nhà cung cấp thành công (Mã: ' . $code . ').');
    }

    public function updateSupplier(Request $request, $id)
    {
        Supplier::findOrFail($id)->update($request->all());
        return redirect()->route('admin.management', ['tab' => 'suppliers'])->with('success', 'Cập nhật nhà cung cấp thành công.');
    }

    public function destroySupplier($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->route('admin.management', ['tab' => 'suppliers'])->with('success', 'Xóa nhà cung cấp thành công.');
    }

    // Department CRUD
    public function storeDepartment(Request $request)
    {
        $request->validate(['department_name' => 'required']);

        // 1. Check if Department already exists (including deleted)
        // "Khoa kỹ thuật" -> "KHOA_KY_THUAT"
        $deptCode = strtoupper(str_replace('-', '_', \Str::slug($request->department_name)));

        $existingDept = \App\Models\Department::withTrashed()
            ->where('department_code', $deptCode)
            ->orWhere('department_name', $request->department_name)
            ->first();

        if ($existingDept) {
            // Restore validation
            if ($existingDept->trashed()) {
                $existingDept->restore();
                $existingDept->update($request->all());
                $dept = $existingDept;
                $msgPrefix = "Đã khôi phục khoa phòng cũ (Mã: $deptCode). ";
            } else {
                // It exists and is active? Rename/Code collision handled?
                // For now, let's assume we use this dept. 
                // Or we can error out saying "Department exists". 
                // Given user context "restore", we reuse it.
                $dept = $existingDept;
                $msgPrefix = "Khoa phòng đã tồn tại (Mã: $deptCode). ";
            }
        } else {
            // Create New
            $dept = \App\Models\Department::create(array_merge($request->all(), ['department_code' => $deptCode]));
            $msgPrefix = "Thêm khoa phòng thành công (Mã: $deptCode). ";
        }

        // 2. Auto-create User Account
        // Username: TMMC-KYTHUAT
        $slug = strtoupper(\Str::slug($request->department_name));
        $userSuffix = str_replace(['KHOA-', 'PHONG-'], '', $slug);
        $userSuffix = str_replace('-', '', $userSuffix);
        $username = 'TMMC-' . $userSuffix;

        $existingUser = \App\Models\User::withTrashed()->where('username', $username)->first();

        if ($existingUser) {
            // Restore and Update
            $existingUser->restore();
            $existingUser->update([
                'active' => true,
                'department_id' => $dept->department_id,
                'full_name' => $request->department_name,
                'password' => \Hash::make('123456')
            ]);
            $msg = $msgPrefix . "Đã khôi phục tài khoản: $username (Mật khẩu: 123456)";
        } else {
            // Create New
            if (\App\Models\User::where('username', $username)->exists()) {
                $username .= rand(1, 9);
            }

            \App\Models\User::create([
                'username' => $username,
                'email' => strtolower($username) . '@tmmc.local',
                'full_name' => $request->department_name,
                'role_code' => 'DEPARTMENT',
                'password' => \Hash::make('123456'),
                'department_id' => $dept->department_id,
                'active' => true
            ]);
            $msg = $msgPrefix . "Đã tạo tài khoản: $username / 123456";
        }

        return redirect()->route('admin.management', ['tab' => 'departments'])->with('success', $msg);
    }

    public function updateDepartment(Request $request, $id)
    {
        \App\Models\Department::findOrFail($id)->update($request->all());
        return redirect()->route('admin.management', ['tab' => 'departments'])->with('success', 'Cập nhật khoa phòng thành công.');
    }

    public function destroyDepartment($id)
    {
        $dept = \App\Models\Department::findOrFail($id);

        // Disable and Soft Delete associated users
        $users = \App\Models\User::where('department_id', $id)->get();
        foreach ($users as $user) {
            $user->update(['active' => false]);
            $user->delete(); // Soft delete
        }

        $dept->delete();
        return redirect()->route('admin.management', ['tab' => 'departments'])->with('success', 'Xóa khoa phòng và vô hiệu hóa tài khoản quản lý thành công.');
    }

    // User CRUD
    public function storeUser(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'full_name' => 'required',
            'role_code' => 'required',
            'password' => 'required|min:6'
        ]);
        $data = $request->all();
        $data['password'] = \Hash::make($request->password);
        \App\Models\User::create($data);
        return redirect()->route('admin.management', ['tab' => 'users'])->with('success', 'Thêm tài khoản thành công.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        // Prevent changing Role if user belongs to a Department (must remove from Department first)
        if ($user->department_id && $request->role_code !== $user->role_code) {
            return redirect()->back()->with('error', 'Không thể thay đổi vai trò của tài khoản đang quản lý Khoa/Phòng. Vui lòng chuyển tài khoản khỏi Khoa trước.');
        }

        $data = $request->only(['full_name', 'department_id', 'role_code', 'active']);
        if ($request->filled('password')) {
            $data['password'] = \Hash::make($request->password);
        }
        $user->update($data);
        return redirect()->route('admin.management', ['tab' => 'users'])->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function toggleUserStatus($id)
    {
        $user = \App\Models\User::findOrFail($id);

        if ($user->user_id == \Auth::id()) {
            return redirect()->back()->with('error', 'Không thể tự khóa tài khoản của chính mình.');
        }

        $user->active = !$user->active;
        $user->save();

        $status = $user->active ? 'Mở khóa' : 'Khóa';
        return redirect()->route('admin.management', ['tab' => 'users'])->with('success', "Đã $status tài khoản {$user->username}.");
    }

    public function destroyUser($id)
    {
        if ($id == \Auth::id()) {
            return redirect()->back()->with('error', 'Không thể xóa tài khoản của chính bạn!');
        }
        \App\Models\User::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Xóa tài khoản thành công.');
    }
    public function approveSummaryVotes(Request $request)
    {
        // Get month/year from query params (default to current month)
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);

        // 1. Get raw items for Detail View - Filter by REQUEST creation date
        $items = PurchaseRequestItem::whereHas('request', function ($query) use ($currentMonth, $currentYear) {
            $query->whereIn('status', ['SUBMITTED', 'APPROVED'])
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth);
        })->with(['product.category.supplier', 'request.department'])
            ->get();

        $groupedByDept = $items->groupBy(function ($item) {
            return $item->request->department_id;
        });

        $departments = \App\Models\Department::orderBy('department_code')->get();

        // Separate logic for "Pending Notifications" (Yêu cầu chờ)
        // Only count items that are TRULY pending action
        $pendingItems = $items->filter(function ($item) {
            return $item->decision_status === 'PENDING' || $item->decision_status === null;
        });

        $pendingGroupedByDept = $pendingItems->groupBy(function ($item) {
            return $item->request->department_id;
        });

        // 2. Sync with Aggregation Logic (DB)
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

        $aggregatedData = $items->groupBy('product_id');
        $validProductIds = []; // Track actual valid products

        foreach ($aggregatedData as $productId => $prodItems) {
            $totalQty = $prodItems->where('decision_status', '!=', 'REJECTED')->sum('quantity_requested');

            if ($totalQty == 0) {
                // If quantity is 0, do NOT add to valid list (so it gets deleted below)
                continue;
            }

            $validProductIds[] = $productId; // Add to valid list

            $product = $prodItems->first()->product;

            AggregationItem::updateOrCreate(
                [
                    'aggregation_batch_id' => $currentBatch->aggregation_batch_id,
                    'product_id' => $productId,
                ],
                [
                    'supplier_id' => $product->supplier_id,
                    'total_approved' => $totalQty,
                ]
            );
        }

        // Cleanup: Remove AggregationItems that are NO LONGER in the pending list OR have 0 quantity
        AggregationItem::where('aggregation_batch_id', $currentBatch->aggregation_batch_id)
            ->whereNotIn('product_id', $validProductIds)
            ->delete();
        // }

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

            // User Request: Only show items that have been created as PO and PO status is ISSUED
            $issuedPOs = PurchaseOrder::where('status', 'ISSUED')
                ->whereYear('order_date', $currentBatch->batch_year)
                ->whereMonth('order_date', $currentBatch->batch_month)
                ->get();

            $issuedPOsExist = $issuedPOs->isNotEmpty();
            $issuedPOIds = $issuedPOs->pluck('purchase_order_id');

            // Get items first to extract product IDs
            $issuedPOItems = PurchaseOrderItem::whereIn('purchase_order_id', $issuedPOIds)
                ->with(['purchaseOrder', 'product'])
                ->get();

            // Merge IDs: Attributes from Aggregation AND actual Issued Items
            $issuedProductIds = $issuedPOItems->pluck('product_id')->unique()->toArray();
            $productIds = array_unique(array_merge($productIds, $issuedProductIds));

            $deptTotalRequested = collect();
            $deptQtyTotals = collect();

            // Calculate Totals per Department
            // Header: From Valid Requests (excluding REJECTED)
            $validRequestItems = $items->where('decision_status', '!=', 'REJECTED');

            foreach ($allDepartments as $dept) {
                // Header (Total Requested - Valid)
                $reqQty = $validRequestItems->where('request.department_id', $dept->department_id)->sum('quantity_requested');
                $deptTotalRequested[$dept->department_id] = $reqQty;

                // Footer (Total Approved/Issued)
                $issuedQty = $issuedPOItems->where('purchaseOrder.department_id', $dept->department_id)->sum('quantity_ordered');
                $deptQtyTotals[$dept->department_id] = $issuedQty;
            }

            // Paginate product IDs manually
            $page = request()->get('page', 1);
            $perPage = 15;
            $paginatedProductIds = collect($productIds)->forPage($page, $perPage);

            // Build pivot: products × departments from issued items
            $pivotData = $issuedPOItems->whereIn('product_id', $paginatedProductIds)
                ->groupBy('product_id')
                ->map(function ($items) use ($allDepartments) {
                    $row = [
                        'product' => $items->first()->product,
                        'departments' => []
                    ];

                    foreach ($allDepartments as $dept) {
                        $deptQty = $items->where('purchaseOrder.department_id', $dept->department_id)->sum('quantity_ordered');
                        $row['departments'][$dept->department_id] = $deptQty > 0 ? $deptQty : null;
                    }

                    $row['total'] = $items->sum('quantity_ordered');
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
            'deptQtyTotals',
            'pendingGroupedByDept',
            'currentMonth',
            'currentYear'
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
                    ->orderBy('purchase_order_id', 'desc') // Fix: Order by ID
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

                    // Create PO Item
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->purchase_order_id,
                        'product_id' => $prodId,
                        'quantity_ordered' => $qty,
                        'unit_price' => $product->unit_price,
                        'total_price' => $lineTotal,
                    ]);
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

            if ($item->decision_status !== 'PENDING' && $item->decision_status !== null) {
                return response()->json(['success' => false, 'message' => 'Mục này đã được xử lý (trạng thái: ' . $item->decision_status . ')'], 422);
            }

            $item->update(['decision_status' => 'APPROVED']);
            \Log::info("Item updated successfully");

            // Check parent request status
            $request = $item->request;
            $allItems = $request->items;

            $approvedCount = $allItems->where('decision_status', 'APPROVED')->count();
            $rejectedCount = $allItems->where('decision_status', 'REJECTED')->count();
            $totalCount = $allItems->count();

            // Update parent request status based on items
            // Update parent request status ONLY if ALL items are handled
            if ($approvedCount + $rejectedCount === $totalCount) {
                if ($approvedCount > 0) {
                    // At least one approved → Request becomes APPROVED
                    $request->update(['status' => 'APPROVED']);
                    \Log::info("Request {$request->purchase_request_id} updated to APPROVED");

                    // Create PO for approved items only
                    $this->createPOForRequest($request);
                } else {
                    // All rejected → Request becomes REJECTED
                    $request->update(['status' => 'REJECTED']);
                    \Log::info("Request {$request->purchase_request_id} updated to REJECTED");
                }
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

            if ($item->decision_status !== 'PENDING' && $item->decision_status !== null) {
                return response()->json(['success' => false, 'message' => 'Mục này đã được xử lý (trạng thái: ' . $item->decision_status . ')'], 422);
            }

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
                ->orderBy('purchase_order_id', 'desc') // Fix: Order by ID to get the latest one including current batch
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
                try {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->purchase_order_id,
                        'product_id' => $item->product_id,
                        'quantity_ordered' => $item->quantity_requested,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                        'aggregation_item_id' => null,
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Failed to create PO Item for PO $poCode: " . $e->getMessage());
                }
            }

            $po->update(['total_amount' => $total]);
            \Log::info("Created PO {$poCode} for request {$request->request_code} with items.");
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

    public function exportAggregationExcel(Request $request)
    {
        // Fetch aggregation data
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);

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

        if (empty($groupedByDepartment)) {
            return back()->with('error', 'Không có dữ liệu chi tiết theo khoa để xuất file Excel.');
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
