<?php

namespace App\Models;

use App\Models\CatalogArea;
use App\Models\CatalogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Catalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sisteco_legacy_id',
    ];

    /**
     * Get the catalog types for the catalog.
     */
    public function catalogTypes()
    {
        return $this->hasMany(CatalogType::class);
    }

    /**
     * Get the catalog areas for the catalog.
     * 
     */
    public function catalogAreas()
    {
        return $this->hasMany(CatalogArea::class);
    }
}
