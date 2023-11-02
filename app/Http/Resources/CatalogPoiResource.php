<?php

namespace App\Http\Resources;

use App\Models\CatalogType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogPoiResource extends JsonResource
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

        $filteredCatalogAreas = DB::table('catalog_areas')->whereNot('catalog_type_id', 1)->get();



        foreach ($filteredCatalogAreas as $catalogArea) {
            $geometry = DB::select("select st_asText(ST_Centroid(geometry)) as baricentro from catalog_areas where id=$catalogArea->id;")[0]->baricentro;
            $geometry = [
                'type' => 'Point',
                'coordinates' => explode(' ', str_replace('POINT(', '', str_replace(')', '', $geometry))),
            ];
            $surface = DB::select("select st_area(geometry) as area from catalog_areas where id=$catalogArea->id;")[0]->area;
            //format surface in hectares
            $surface = round($surface / 10000, 2);
            $surface = number_format($surface, 2, ',', '.');
            //format estimated_value to euros
            $estimatedValue = number_format($catalogArea->estimated_value, 2, ',', '.');
            $catalogType = CatalogType::where('id', $catalogArea->catalog_type_id)->first();



            $output['features'][] = [
                'type' => 'Feature',
                'properties' => [
                    'surface' => $surface . ' ha',
                    'catalog_type code_int' => $catalogType->code_int,
                    'catalog_type name' => $catalogType->name,
                    'estimated_value' => $estimatedValue,
                    'public_url' => url('catalog-areas/' . $catalogArea->id),
                ],
                'geometry' => $geometry,
            ];
        }

        return $output;
    }
}