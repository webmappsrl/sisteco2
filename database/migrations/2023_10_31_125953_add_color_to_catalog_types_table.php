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
        Schema::table('catalog_types', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->float('maintenance_price_fist_year')->nullable();
            $table->float('maintenance_price_second_year')->nullable();
            $table->float('maintenance_price_third_year')->nullable();
            $table->float('maintenance_price_fourth_year')->nullable();
            $table->float('maintenance_price_fifth_year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_types', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->dropColumn('maintenance_price_fist_year');
            $table->dropColumn('maintenance_price_second_year');
            $table->dropColumn('maintenance_price_third_year');
            $table->dropColumn('maintenance_price_fourth_year');
            $table->dropColumn('maintenance_price_fifth_year');
        });
    }
};
