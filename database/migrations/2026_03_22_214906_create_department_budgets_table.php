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
        Schema::create('department_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->year('year'); // Năm ngân sách (VD: 2026)
            $table->decimal('total_budget', 15, 2)->default(0); // Tổng ngân sách được cấp
            $table->decimal('used_budget', 15, 2)->default(0); // Ngân sách đã sử dụng
            $table->decimal('remaining_budget', 15, 2)->default(0); // Ngân sách còn lại
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
            
            // Unique constraint: một khoa chỉ có một ngân sách cho một năm
            $table->unique(['department_id', 'year'], 'unique_department_year_budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_budgets');
    }
};
