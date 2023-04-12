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

        // Build the output
        $output = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => json_decode($parcel_geometry, true),
                    'properties' => []
                ]
            ]
        ];

        // Add the geometries from the catalog
        foreach ($catalog_geometries['features'] as $geometry) {
            $output['features'][] = [
                $geometry
            ];
        }

        // Return the output
        return $output;
    }
}