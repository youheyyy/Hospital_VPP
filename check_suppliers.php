<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::whereIn('product_name', ['Giấy A4 Double A', 'Găng tay y tế'])->get();
foreach ($products as $p) {
    echo "Product: {$p->product_name} - Supplier ID: " . ($p->supplier_id ?? 'NULL') . "\n";
}
