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
        Schema::create('cadastral_parcels', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code');
            $table->string('municipality')->nullable();
            $table->float('estimated_value')->nullable();
            $table->float('average_slope')->nullable();
            $table->integer('meter_min_distance_road')->nullable();
            $table->integer('meter_min_distance_path')->nullable();
            $table->float('square_meter_surface')->nullable();
            $table->integer('slope')->nullable();
            $table->integer('way')->nullable();
            $table->json('catalog_estimate')->nullable();
            $table->multiPolygon('geometry')->nullable();
            $table->bigInteger('sisteco_legacy_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastral_parcels');
    }
};