<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aggregation_batches', function (Blueprint $table) {
            $table->id('aggregation_batch_id');
            $table->string('batch_code', 20)->nullable(); // AGG-2026-01
            $table->integer('batch_month');
            $table->integer('batch_year');
            $table->string('status', 20); // DRAFT, APPROVED

            $table->timestamps();
            // Note: User spec didn't explicitly ask for softDeletes here, but standard practice.
            // Wait, looking at spec: "created_by, created_at, updated_at". No soft deletes mentioned for this one in spec explicitly?
            // "Không xóa, chỉ đổi trạng thái" -> implied logical delete or just status change.
            // Spec lists created_by, created_at, updated_at only. I will follow spec strictly where distinct.
            // Actually, for consistency and "Không xóa" usually means soft deletes or just status management.
            // Re-reading spec for AGGREGATION_BATCHES:
            // "created_by, created_at, updated_at". NO deleted_at listed in the text block provided by user.
            // User requested: "Hãy tạo, không làm sai sót hay mất bất kì trường thuộc tính dữ liệu nào của tôi nhé"
            // So I will stick EXACTLY to the fields listed.

            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aggregation_batches');
    }
};
