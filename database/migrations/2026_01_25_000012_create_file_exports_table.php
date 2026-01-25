<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_exports', function (Blueprint $table) {
            $table->id('file_export_id');
            $table->string('file_code')->nullable(); // Spec didn't specify length, using default string length or user might want specific.
            // Spec says "file_code", "file_name" without types but implied string.
            // Other tables used 50 or 20. I'll use default 255 for flexibility unless key.
            $table->string('file_name')->nullable();

            $table->unsignedBigInteger('aggregation_batch_id');
            $table->string('report_type', 30);
            $table->string('file_path', 255);

            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->index('aggregation_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_exports');
    }
};
