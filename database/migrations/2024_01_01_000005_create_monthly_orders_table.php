<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->string('month', 7); // Format: MM/YYYY hoặc 09/2025
            $table->decimal('quantity', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint: một khoa chỉ có một yêu cầu cho một sản phẩm trong một tháng
            $table->unique(['department_id', 'product_id', 'month'], 'unique_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_orders');
    }
};
