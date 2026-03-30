<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_form')) {
                $table->boolean('is_form')->default(false)->after('unit');
            }
            if (!Schema::hasColumn('products', 'paper_size')) {
                $table->string('paper_size', 10)->nullable()->after('is_form');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_form', 'paper_size']);
        });
    }
};
