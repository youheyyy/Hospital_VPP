<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aggregation_items', function (Blueprint $table) {
            $table->id('aggregation_item_id');
            $table->unsignedBigInteger('aggregation_batch_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->integer('total_requested')->nullable();
            $table->integer('total_approved')->nullable();

            // NOT_ISSUED, PARTIAL_ISSUED, ISSUED
            $table->string('fulfilled_type', 20)->nullable();

            $table->text('note')->nullable();

            $table->timestamps(); // created_at, updated_at

            $table->index('aggregation_batch_id');
            $table->index('product_id');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aggregation_items');
    }
};
