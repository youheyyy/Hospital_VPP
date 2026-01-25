<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id('purchase_order_item_id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('aggregation_item_id');

            // No timestamps listed in spec for this table

            $table->index('purchase_order_id');
            $table->index('aggregation_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
