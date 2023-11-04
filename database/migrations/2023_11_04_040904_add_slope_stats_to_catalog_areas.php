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
            $table->float('slope_min')->default(0);
            $table->float('slope_max')->default(0);
            $table->float('slope_avg')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_areas', function (Blueprint $table) {
            $table->dropColumn('slope_min');
            $table->dropColumn('slope_max');
            $table->dropColumn('slope_avg');
        });
    }
};
