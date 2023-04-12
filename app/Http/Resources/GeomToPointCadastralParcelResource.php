<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class GeomToPointCadastralParcelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $geometry = DB::select('select st_asGeojson(st_centroid(geometry)) as geom from cadastral_parcels where id=680;')[0]->geom;
        return [
            'id' => $this->id,
            'name' => [
                'it' => $this->code,
            ],
            'description' => [
                'it' => 'TO BE IMPLEMENTED',
            ],
            'related_url' => [
                'https://sisteco.maphub.it/cadastral-parcels/'.$this->id,
            ],
            'geometry' => json_decode($geometry,true),

        ];
    }
}
