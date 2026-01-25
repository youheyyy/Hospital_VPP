<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id('purchase_request_id');
            $table->string('request_code', 50)->unique()->nullable();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('requester_id');
            $table->date('request_date')->nullable();
            $table->string('status', 20); // DRAFT, SUBMITTED, AGGREGATED

            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index('department_id');
            $table->index('requester_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
