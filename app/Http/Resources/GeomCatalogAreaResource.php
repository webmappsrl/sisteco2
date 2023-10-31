<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class GeomCatalogAreaResource extends JsonResource
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
        $catalog_area_geometry = DB::select("select st_asGeojson(geometry) as geom from catalog_areas where id=$this->id;")[0]->geom;
        $geometry = json_decode($catalog_area_geometry, true);

        $output['features'][] =
            [
                'type' => 'Feature',
                'properties' => [
                    'id' => $this->id,
                    'type_sisteco' => 'Catalog Area',
                    'cod_int' => $this->catalogType->cod_int,
                    'strokeColor' => config('sisteco.areaStyle.' . $this->catalogType->cod_int . '.strokeColor'),
                    'fillColor' => config('sisteco.areaStyle.' . $this->catalogType->cod_int . '.fillColor'),
                ],
                'geometry' => $geometry,
            ];

        return $output;
    }
}
