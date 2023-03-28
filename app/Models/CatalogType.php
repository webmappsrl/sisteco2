<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cod_int',
        'catalog_id',
        'sisteco_legacy_id',
    ];

    /**
     * Get the catalog that owns the catalog type.
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }

    /**
     * Get the catalog areas for the catalog type.
     */

    public function catalogAreas()
    {
        return $this->hasMany(CatalogArea::class);
    }
}
