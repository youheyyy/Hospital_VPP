<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id('request_item_id');
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('product_id');

            $table->integer('quantity_requested');
            $table->integer('quantity_approved')->nullable();
            $table->string('decision_status', 20); // SUBMITTED, APPROVED, REJECTED

            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index('purchase_request_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
