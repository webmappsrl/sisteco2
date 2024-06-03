<?php

namespace App\Http\Resources;

use App\Models\CatalogType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogGeohubResource extends JsonResource
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
            $surface = DB::select("select st_area(geometry) as area from catalog_areas where id=$catalogArea->id;")[0]->area;
            //format surface in hectares
            $surface = round($surface / 10000, 2);
            $surface = number_format($surface, 2, ',', '.');
            //format estimated_value to euros
            $estimatedValue = number_format($catalogArea->estimated_value, 2, ',', '.');
            $catalogType = CatalogType::where('id', $catalogArea->catalog_type_id)->first();
            $detailsLink = url('catalog-areas/' . $catalogArea->id);
            $detailsLinkHtml = "<a href=\"$detailsLink\">Link</a>";
            $htmlString = "superficie: $surface ha, Codice Intervento: $catalogType->cod_int, Tipo di Intervento: $catalogType->name, Valore stimato: $estimatedValue € Dettagli: $detailsLinkHtml";
            $output['features'][] = [
                'type' => 'Feature',
                'properties' => [
                    'surface' => $surface . ' ha',
                    'catalog_type_cod_int' => $catalogType->cod_int,
                    'catalog_type name' => $catalogType->name,
                    'estimated_value' => $estimatedValue . '€',
                    'public_url' => url('catalog-areas/' . $catalogArea->id),
                    'popup' => [
                        'html' => $htmlString
                    ],
                ],
                'geometry' => json_decode(DB::select("select st_asGeojson(geometry) as geom from catalog_areas where id=$catalogArea->id;")[0]->geom, true),
            ];
        };
        return $output;
    }
}
