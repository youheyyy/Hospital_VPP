<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('full_name', 100)->nullable();
            $table->string('password'); // 255 by default

            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('role_code', 20); // ADMIN / DEPARTMENT
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Foreign key usually handled separately or here if confident order is right.
            // Since we number them, we can add simple index or constrain if strict.
            // User requested "department_id BIGINT", will add index for performance.
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
