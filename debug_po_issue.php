<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Product;
use App\Models\PurchaseOrder;

// 1. Check Approved Requests
$approvedRequests = PurchaseRequest::where('status', 'APPROVED')->get();
echo "Approved Requests Count: " . $approvedRequests->count() . "\n";

// 2. Check Products without Supplier
$items = PurchaseRequestItem::whereIn('purchase_request_id', $approvedRequests->pluck('purchase_request_id'))
    ->with('product')
    ->get();

$productsWithoutSupplier = [];
foreach ($items as $item) {
    if (!$item->product->supplier_id) {
        $productsWithoutSupplier[$item->product_id] = $item->product->product_name;
    }
}

echo "Products without Supplier: " . count($productsWithoutSupplier) . "\n";
print_r($productsWithoutSupplier);

// 3. POs
echo "POs Count: " . PurchaseOrder::count() . "\n";
