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

        $output = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];


        // First Add catalog feature

        // Get the geometries from the catalog
        $catalog_geometries = $this->getCatalogGeometries(1);

        // Add the geometries from the catalog
        if (count($catalog_geometries) > 0) {
            foreach ($catalog_geometries as $geometry) {
                $output['features'][] = [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $geometry['id'],
                        'type_sisteco' => 'Catalog Area',
                        'cod_int' => $geometry['cod_int'],
                        'strokeColor' => config('sisteco.areaStyle.' . $geometry['cod_int'] . '.strokeColor'),
                        'fillColor' => config('sisteco.areaStyle.' . $geometry['cod_int'] . '.fillColor'),


                    ],
                    'geometry' => $geometry['geometry'],
                ];
            }
        }

        // Add cadastral parcel feature

        // Get the geometry of the current cadastral parcel
        $parcel_geometry = DB::select("select st_asGeojson(geometry) as geom from cadastral_parcels where id=$this->id;")[0]->geom;



        $output['features'][] =
            [
                'type' => 'Feature',
                'properties' => [
                    'id' => $this->id,
                    'type_sisteco' => 'Cadastral Parcel',
                    'strokeColor' => config('sisteco.areaStyle.cadastral.strokeColor'),
                    'fillColor' => config('sisteco.areaStyle.cadastral.fillColor'),
                ],
                'geometry' => json_decode($parcel_geometry, true),
            ];

        // Return the output
        return $output;
    }
}
