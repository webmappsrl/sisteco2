<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'sisteco_legacy_id',
        'first_name',
        'last_name',
        'email',
        'business_name',
        'vat_number',
        'fiscal_code',
        'phone',
        'addr:street',
        'addr:housenumber',
        'addr:city',
        'addr:postcode',
        'addr:locality',
    ];


    /**
     * Get the cadastral parcels that belong to the Owner.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cadastralParcels(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CadastralParcel::class);
    }
}
