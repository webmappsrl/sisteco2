<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class GeomCadastralParcelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $geometry = DB::select("select st_asGeojson(geometry) as geom from cadastral_parcels where id=$this->id;")[0]->geom;
        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $this->id,
                'code' => $this->code,
            ],
            'geometry' => json_decode($geometry, true) 
        ];
    }
}
