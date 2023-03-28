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
        Schema::create('catalog_areas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->multiPolygon('geometry');
            $table->foreingId('catalog_type_id')->constrained();
            $table->foreignId('catalog_id')->constrained();
            $table->bigInteger('sisteco_legacy_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_areas');
    }
};
