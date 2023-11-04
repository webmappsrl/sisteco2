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
            $table->float('hiking_routes_min_dist')->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_areas', function (Blueprint $table) {
            $table->dropColumn('hiking_routes_min_dist');
        });
    }
};
