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
}
