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
                    'type' => 'feature',
                    'properties' => [
                        'id' => $this->id,
                        'type_sisteco' => 'Cadastral Parcel',
                        'strokeColor' => config('sisteco.cadastralParcelAreaStyle.cadastral.strokeColor'),
                        'fillColor' => config('sisteco.cadastralParcelAreaStyle.cadastral.fillColor'),
                        'opacity' => config('sisteco.cadastralParcelAreaStyle.cadastral.opacity'),

                    ],
                    'geometry' => json_decode($parcel_geometry, true),
                ]
            ]
        ];

        // Add the geometries from the catalog
        foreach ($catalog_geometries as $geometry) {
            $output['features'][] = [
                'type' => 'feature',
                'properties' => [
                    'id' => $geometry['id'],
                    'type_sisteco' => 'Catalog Area',
                    'cod_int' => $geometry['cod_int'],
                    'strokeColor' => config('sisteco.cadastralParcelAreaStyle.' . $geometry['cod_int'] . '.strokeColor'),
                    'fillColor' => config('sisteco.cadastralParcelAreaStyle.' . $geometry['cod_int'] . '.fillColor'),
                    'opacity' => config('sisteco.cadastralParcelAreaStyle.' . $geometry['cod_int'] . '.opacity'),

                ],
                'geometry' => $geometry['geometry'],
            ];
        }

        // Return the output
        return $output;
    }
}