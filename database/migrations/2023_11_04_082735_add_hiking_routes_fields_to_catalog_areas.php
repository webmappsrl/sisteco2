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
            $table->float('hiking_routes_length')->default(0.0);
            $table->json('hiking_routes_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_areas', function (Blueprint $table) {
            $table->dropColumn('hiking_routes_length');
            $table->dropColumn('hiking_routes_details');
        });
    }
};
