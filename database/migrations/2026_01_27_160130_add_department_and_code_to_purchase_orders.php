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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('supplier_id');
            $table->string('order_code', 50)->nullable()->after('purchase_order_id');
            $table->date('order_date')->nullable()->after('status');
            $table->decimal('total_amount', 15, 2)->nullable()->after('order_date');

            $table->index('department_id');
            $table->index('order_code');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['order_code']);
            $table->dropColumn(['department_id', 'order_code', 'order_date', 'total_amount']);
        });
    }
};
