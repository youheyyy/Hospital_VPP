<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Category;
use App\Models\Product;
use App\Models\MonthlyOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Hiển thị dashboard admin
     */
    public function dashboard(Request $request)
    {
        $selectedMonth = $request->input('month', date('m/Y'));
        $totalRequests = MonthlyOrder::where('month', $selectedMonth)->count();
        $totalCost = 42850000;

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

        $departments = Department::all();
        $departmentStats = [];

        foreach ($departments as $dept) {
            $orderCount = MonthlyOrder::where('department_id', $dept->id)
                ->where('month', $selectedMonth)
                ->sum('quantity');

            $departmentStats[] = [
                'name' => $dept->name,
                'value' => $orderCount * 100000,
            ];
        }

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
        $selectedMonth = $request->input('month', date('m/Y'));
        $selectedCategory = $request->input('category');

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $categoriesQuery = Category::orderBy('display_order');
        if ($selectedCategory) {
            $categories = $categoriesQuery->where('id', $selectedCategory)->get();
        } else {
            $categories = $categoriesQuery->get();
        }

        $productsQuery = Product::with([
            'category',
            'monthlyOrders' => function ($query) use ($selectedMonth) {
                $query->where('month', $selectedMonth)->with('department');
            }
        ])
            ->orderBy('category_id')
            ->orderBy('display_order');

        if ($selectedCategory) {
            $productsQuery->where('category_id', $selectedCategory);
        }

        $products = $productsQuery->get()->groupBy('category_id');
        $allCategories = Category::orderBy('display_order')->get();

        $categoryTotals = [];
        foreach ($products as $categoryId => $categoryProducts) {
            $categoryTotal = 0;
            foreach ($categoryProducts as $product) {
                foreach ($product->monthlyOrders as $order) {
                    if ($order->month == $selectedMonth) {
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryTotal += $order->quantity * $price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        $grandTotal = array_sum($categoryTotals);

        // Tính toán tổng hợp Biểu mẫu theo khổ giấy
        $paperSummary = [
            'A3' => ['sheets' => 0, 'grams' => 0],
            'A4' => ['sheets' => 0, 'grams' => 0],
            'A5' => ['sheets' => 0, 'grams' => 0],
        ];

        $formOrders = \App\Models\MonthlyOrder::where('month', $selectedMonth)
            ->whereHas('product', function($q) {
                $q->where('is_form', true);
            })
            ->with('product')
            ->get();

        foreach ($formOrders as $order) {
            $size = strtoupper($order->product->paper_size ?? '');
            if (isset($paperSummary[$size])) {
                $qty = $order->quantity;
                $unit = mb_strtolower($order->product->unit ?? '');
                if (str_contains($unit, 'cuốn') || str_contains($unit, 'sổ') || str_contains($unit, 'quyển')) {
                    $qty *= 60;
                }
                $paperSummary[$size]['sheets'] += $qty;
            }
        }

        foreach ($paperSummary as $size => &$data) {
            $data['grams'] = ceil($data['sheets'] / 500);
        }

        return view('admin.consolidated', compact(
            'departments',
            'categories',
            'allCategories',
            'products',
            'selectedMonth',
            'categoryTotals',
            'grandTotal',
            'paperSummary'
        ));
    }

    /**
     * Export consolidated data to Excel
     */
    public function exportConsolidated(Request $request)
    {
        $selectedMonth = $request->input('month', date('m/Y'));
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
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryTotal += $order->quantity * $price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        $grandTotal = array_sum($categoryTotals);
        $filename = 'Tong_hop_VPP_' . str_replace('/', '_', $selectedMonth) . '_' . date('YmdHis') . '.xlsx';

        $export = new \App\Exports\ConsolidatedExport(
            $selectedMonth, $departments, $categories, $products, $categoryTotals, $grandTotal
        );

        return $export->download($filename);
    }

    /**
     * Export specialized Biểu mẫu (Detailed)
     */
    public function exportBiemMau(Request $request)
    {
        $selectedMonth = $request->input('month', date('m/Y'));
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
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryTotal += $order->quantity * $price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        $grandTotal = array_sum($categoryTotals);
        $filename = 'Export_Bieu_Mau_' . str_replace('/', '_', $selectedMonth) . '.xlsx';

        // mode = 'detailed_biemmau'
        $export = new \App\Exports\ConsolidatedExport(
            $selectedMonth, $departments, $categories, $products, $categoryTotals, $grandTotal, 'all', null, 'detailed_biemmau'
        );

        return $export->download($filename);
    }

    /**
     * Export specialized Tổng VPP (Aggregated Forms)
     */
    public function exportTongVPP(Request $request)
    {
        $selectedMonth = $request->input('month', date('m/Y'));
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
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryTotal += $order->quantity * $price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }
        $grandTotal = array_sum($categoryTotals);

        $export = new \App\Exports\ConsolidatedExport(
            $selectedMonth, $departments, $categories, $products, $categoryTotals, $grandTotal, 'all', null, 'aggregated_vpp'
        );

        return $export->download('Export_Tong_VPP_' . str_replace('/', '_', $selectedMonth) . '.xlsx');
    }

    public function exportTongVPPAll(Request $request)
    {
        $selectedMonth = $request->input('month', date('m/Y'));
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
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryTotal += $order->quantity * $price;
                    }
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }
        $grandTotal = array_sum($categoryTotals);

        // Use the NEW specialized export class
        $export = new \App\Exports\ConsolidatedAllExport(
            $selectedMonth, $departments, $categories, $products, $categoryTotals, $grandTotal, 'all', null
        );

        return $export->download('Export_Tong_VPP_Chung_' . str_replace('/', '_', $selectedMonth) . '.xlsx');
    }


    /**
     * Xuất Excel cho giao diện hiện tại
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
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryTotal += $order->quantity * $price;
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
            $selectedMonth, $departments, $categories, $products, $categoryTotals, $grandTotal, $tabType, $deptId
        );

        return $export->download($filename);
    }

    /**
     * Print/PDF view for consolidated data
     */
    public function printConsolidated(Request $request)
    {
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
                        $price = ($order->price && $order->price > 0) ? $order->price : $order->product->price;
                        return $order->quantity * $price;
                    });

                return view('department.department-print', compact(
                    'department', 'orders', 'selectedMonth', 'totalAmount'
                ));
            }
        }

        $categories = Category::where('is_active', true)->orderBy('display_order')->get();

        $products = Product::where('is_active', true)
            ->with([
                'category',
                'monthlyOrders' => function ($query) use ($selectedMonth) {
                    $query->where('month', $selectedMonth)->with('department');
                }
            ])
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->get()
            ->groupBy('category_id');

        if ($tabType === 'bang_tong') {
            return view('admin.print-bang-tong', compact(
                'departments', 'categories', 'products', 'selectedMonth'
            ));
        }

        $categoryTotals = [];
        foreach ($products as $categoryId => $categoryProducts) {
            $categoryTotal = 0;
            foreach ($categoryProducts as $product) {
                foreach ($product->monthlyOrders as $order) {
                    $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                    $categoryTotal += $order->quantity * $price;
                }
            }
            $categoryTotals[$categoryId] = $categoryTotal;
        }

        $grandTotal = array_sum($categoryTotals);

        $paperSizeSummary = [
            'A3' => ['sheets' => 0, 'grams' => 0],
            'A4' => ['sheets' => 0, 'grams' => 0],
            'A5' => ['sheets' => 0, 'grams' => 0],
        ];

        $formOrders = \App\Models\MonthlyOrder::where('month', $selectedMonth)
            ->whereHas('product', function($q) { $q->where('is_form', true); })
            ->with('product')
            ->get();

        foreach ($formOrders as $order) {
            $size = strtoupper($order->product->paper_size ?? '');
            if (isset($paperSizeSummary[$size])) {
                $qty = $order->quantity;
                $unit = mb_strtolower($order->product->unit ?? '');
                if (str_contains($unit, 'cuốn') || str_contains($unit, 'sổ') || str_contains($unit, 'quyển')) {
                    $qty *= 60;
                }
                $paperSizeSummary[$size]['sheets'] += $qty;
            }
        }

        foreach ($paperSizeSummary as $size => &$data) {
            $data['grams'] = ceil($data['sheets'] / 500);
        }

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
                        $price = ($order->price && $order->price > 0) ? $order->price : $product->price;
                        $categoryData['orders'][] = [
                            'product' => $product,
                            'order' => $order,
                            'total' => $order->quantity * $price
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
            'departments', 'categories', 'products', 'selectedMonth',
            'categoryTotals', 'grandTotal', 'departmentOrders', 'paperSizeSummary'
        ));
    }

    public function updateNote(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'month' => 'required',
            'note' => 'nullable|string',
        ]);

        $newSharedNote = $request->note ?? '';

        $orders = MonthlyOrder::where('product_id', $request->product_id)
            ->where('month', $request->month)
            ->get();

        foreach ($orders as $order) {
            $currentNotes = $order->admin_notes ?? '';
            $parts = explode('|||', $currentNotes);
            $privatePart = isset($parts[1]) ? trim($parts[1]) : '';

            if ($privatePart !== '') {
                $order->admin_notes = $newSharedNote . ' ||| ' . $privatePart;
            } else {
                $order->admin_notes = $newSharedNote;
            }

            $order->save();
        }

        return response()->json(['success' => true, 'affected' => $orders->count()]);
    }

    public function updatePrivateNote(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'department_id' => 'required|exists:departments,id',
            'month' => 'required',
            'note' => 'nullable|string',
        ]);

        $order = MonthlyOrder::where([
            'product_id' => $request->product_id,
            'department_id' => $request->department_id,
            'month' => $request->month,
        ])->first();

        if (!$order) {
            $order = new MonthlyOrder();
            $order->product_id = $request->product_id;
            $order->department_id = $request->department_id;
            $order->month = $request->month;
            $order->quantity = 0;
        }

        $currentAdminNotes = $order->admin_notes ?? '';
        $parts = explode('|||', $currentAdminNotes);
        $sharedPart = trim($parts[0]);
        $newPrivateNote = $request->note ?? '';

        if ($newPrivateNote !== '') {
            $order->admin_notes = $sharedPart . ' ||| ' . $newPrivateNote;
        } else {
            $order->admin_notes = $sharedPart;
        }

        $order->save();

        // Calculate the display note (similar to how it's done in the initial Blade view)
        $displayNotes = [];
        if ($order->notes) {
            $displayNotes[] = $order->notes;
        }
        if ($newPrivateNote !== '') {
            $displayNotes[] = $newPrivateNote;
        }
        $displayNote = implode(' - ', $displayNotes);

        return response()->json([
            'success' => true,
            'display_note' => $displayNote
        ]);
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'department_id' => 'required|exists:departments,id',
            'month' => 'required',
            'quantity' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $product = Product::find($request->product_id);

        $order = MonthlyOrder::where([
            'product_id' => $request->product_id,
            'department_id' => $request->department_id,
            'month' => $request->month,
        ])->first();

        if (!$order) {
            $order = new MonthlyOrder();
            $order->product_id = $request->product_id;
            $order->department_id = $request->department_id;
            $order->month = $request->month;
        }

        $order->quantity = $request->quantity ?? 0;

        $newPrivateNote = $request->reason ?? '';
        $currentAdminNotes = $order->admin_notes ?? '';
        $parts = explode('|||', $currentAdminNotes);
        $sharedPart = trim($parts[0]);

        if ($newPrivateNote !== '') {
            $order->admin_notes = $sharedPart . ' ||| ' . $newPrivateNote;
        } else {
            $order->admin_notes = $sharedPart;
        }

        if (!$order->price || $order->price == 0) {
            $order->price = $product->price;
        }

        $order->save();

        return response()->json(['success' => true, 'order_id' => $order->id, 'price' => $order->price]);
    }

    public function products()
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalSuppliers = Category::where('is_active', true)->count();

        $categories = Category::with(['products' => function($q) {
                $q->where('is_active', true)->orderBy('display_order');
            }])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('admin.products', compact('totalProducts', 'totalSuppliers', 'categories'));
    }

    public function updateProductPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Product::find($request->product_id);
        $product->previous_price = $product->price;

        MonthlyOrder::where('product_id', $product->id)
            ->where(function($q) {
                $q->whereNull('price')->orWhere('price', 0);
            })
            ->update(['price' => $product->price]);

        $product->price = $request->price;
        $product->save();

        return response()->json(['success' => true]);
    }

    public function updateProductCategory(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->update(['category_id' => $request->category_id]);
        return response()->json(['success' => true]);
    }

    public function updateProductIsForm(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->update(['is_form' => $request->is_form]);
        return response()->json(['success' => true]);
    }

    public function updateProductPaperSize(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->update(['paper_size' => $request->paper_size]);
        return response()->json(['success' => true]);
    }

    public function updateCategoryName(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        $category = Category::find($request->category_id);
        $category->name = $request->name;
        $category->save();

        return response()->json(['success' => true]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'display_order' => Category::max('display_order') + 1,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Đã thêm nhà cung cấp: ' . $category->name);
    }

    public function updateProductName(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
        ]);

        $product = Product::find($request->product_id);
        $product->name = $request->name;
        $product->save();

        return response()->json(['success' => true]);
    }

    public function updateProductUnit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit' => 'required|string|max:50',
        ]);

        $product = Product::find($request->product_id);
        $product->unit = $request->unit;
        $product->save();

        return response()->json(['success' => true]);
    }

    public function destroyProduct(Product $product)
    {
        if ($product->monthlyOrders()->count() > 0) {
            $product->is_active = false;
            $product->save();
            return response()->json(['success' => true, 'message' => 'Sản phẩm đã có dữ liệu, đã chuyển sang trạng thái ngưng hoạt động.']);
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm thành công.']);
    }

    public function destroyCategory(Category $category)
    {
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa nhà cung cấp này vì vẫn còn sản phẩm thuộc về họ.'
            ], 422);
        }

        $category->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa nhà cung cấp thành công.']);
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
        ]);

        Product::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'display_order' => Product::where('category_id', $request->category_id)->max('display_order') + 1,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Đã thêm sản phẩm mới');
    }
}
