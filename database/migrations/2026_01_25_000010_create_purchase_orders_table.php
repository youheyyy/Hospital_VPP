<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('purchase_order_id');
            $table->unsignedBigInteger('aggregation_batch_id');
            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->enum('status', ['APPROVED', 'PURCHASED']); // APPROVED, PURCHASED
            $table->string('proof_image_path', 255)->nullable()->comment('Ảnh chứng từ mua ngoài đã ký');

            $table->dateTime('purchased_at')->nullable();
            $table->unsignedBigInteger('purchased_by')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->index('aggregation_batch_id');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
