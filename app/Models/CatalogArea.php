<?php

namespace App\Models;

use App\Models\Catalog;
use App\Models\CatalogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatalogArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'geometry',
        'catalog_type_id',
        'catalog_id',
        'sisteco_legacy_id',
    ];

    /**
     * Get the catalog type that owns the catalog area.
     */
    public function catalogType()
    {
        return $this->belongsTo(CatalogType::class);
    }

    /**
     * Get the catalog that owns the catalog area.
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }
}
