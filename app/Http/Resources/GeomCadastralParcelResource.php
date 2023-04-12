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

        // Get the geometries from the catalog
        $catalog_geometries = $this->getCatalogGeometries(1);

        // Get the geometry of the current cadastral parcel
        $parcel_geometry = DB::select("select st_asGeojson(geometry) as geom from cadastral_parcels where id=$this->id;")[0]->geom;

        $output = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Cadastral Parcel',
                    'id' => $this->id,
                    'properties' => [],
                    'cod_int' => $this->cod_int,
                    'geometry' => json_decode($parcel_geometry, true),
                ]
            ]
        ];

        // Add the geometries from the catalog
        foreach ($catalog_geometries as $geometry) {
            $output['features'][] = [
                $geometry
            ];
        }

        // Return the output
        return $output;
    }
}
