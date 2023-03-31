<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CadastralParcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'municipality',
        'estimated_value',
        'average_slope',
        'meter_min_distance_road',
        'meter_min_distance_path',
        'square_meter_surface',
        'slope',
        'way',
        'catalog_estimate',
        'geometry',
        'sisteco_legacy_id',
    ];

    protected $casts = [
        'catalog_estimate' => 'array',

    ];


    /**
     * Get the owners that own the CadastralParcel.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owners(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Owner::class);
    }

    /**
     * It returns the slope class:
     * 'A' -> s < 20 deg
     * 'B' -> 20 < s <=40 deg
     * 'C' -> s > 40
     *
     * @return string
     */
    public function computeSlopeClass(): string
    {
        if ($this->average_slope <= 20) return 'A';
        if ($this->average_slope <= 40) return 'B';
        return 'C';
    }

    /**
     * It returns the transport class
     * '1' -> d <= 500
     * '2' -> 500 < d <=1000
     * '3' -> 1000 >= 1000
     *
     * @return string
     */
    public function computeTransportClass(): string
    {
        if ($this->meter_min_distance_road <= 500) return '1';
        if ($this->meter_min_distance_road <= 1000) return '2';
        return '3';
    }
}
