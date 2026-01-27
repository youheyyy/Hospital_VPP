<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('aggregation_item_id');
            $table->integer('quantity_ordered')->nullable()->after('product_id');
            $table->decimal('unit_price', 15, 2)->nullable()->after('quantity_ordered');
            $table->decimal('total_price', 15, 2)->nullable()->after('unit_price');

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropColumn(['product_id', 'quantity_ordered', 'unit_price', 'total_price']);
        });
    }
};
