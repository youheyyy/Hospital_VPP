<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

// Logic: Requests that are APPROVED but not linked to any PO item? 
// PO linking is weak. 
// Easier: Revert ALL 'APPROVED' requests created today/recently IF total PO count is 0?
// Or just revert specific IDs found previously.
// Based on debugging output, there are 2 requests. 
// Let's revert specific IDs if possible, or just all APPROVED since we know PO count is 0.

if (PurchaseOrder::count() == 0) {
    $updated = PurchaseRequest::where('status', 'APPROVED')->update(['status' => 'SUBMITTED']);
    echo "Reverted $updated requests to SUBMITTED.\n";

    // Also reset decision_status of items?
    // processAggregation creates PO then updates items to APPROVED.
    // If no PO created, loop didn't run, so item decision_status might still be pending/default?
    // Let's check item decision_status.

    \App\Models\PurchaseRequestItem::whereHas('request', function ($q) {
        $q->where('status', 'SUBMITTED'); // now submitted
    })->update(['decision_status' => 'PENDING']); // Reset to PENDING? 
    // Wait, DB default is? Migration says 'PENDING'.
    // Code update checks: $item->update(['decision_status' => 'APPROVED']);
    // So we should revert items too.

    echo "Reset item statuses.\n";
} else {
    echo "POs exist, unsafe to revert bulk.\n";
}
