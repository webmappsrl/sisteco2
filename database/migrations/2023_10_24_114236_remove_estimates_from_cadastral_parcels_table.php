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
        Schema::table('cadastral_parcels', function (Blueprint $table) {
            $table->dropColumn('estimated_value');
            $table->dropColumn('catalog_estimate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadastral_parcels', function (Blueprint $table) {
            $table->float('estimated_value')->nullable();
            $table->json('catalog_estimate')->nullable();
        });
    }
};
