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
        Schema::table('catalog_areas', function (Blueprint $table) {
            $table->float('estimated_value')->nullable();
            $table->json('catalog_estimate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_areas', function (Blueprint $table) {
            $table->dropColumn('estimated_value');
            $table->dropColumn('catalog_estimate');
        });
    }
};
