<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CatalogTypeAreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $output = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($this->catalogAreas as $catalogArea) {
            $output['features'][] = [
                'type' => 'Feature',
                'geometry' => json_decode(DB::select("select st_asGeojson(geometry) as geom from catalog_areas where id=$catalogArea->id;")[0]->geom, true),
            ];
        }

        return $output;
    }
}
