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
            $table->text('generated_ecosystem_servicesal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_types', function (Blueprint $table) {
            $table->dropColumn('generated_ecosystem_servicesal');
        });
    }
};
