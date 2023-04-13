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
        $geometry = DB::select("select st_asGeojson(st_centroid(geometry)) as geom from cadastral_parcels where id=$this->id;")[0]->geom;
        $break = "<br>";
        $area = number_format($this->square_meter_surface, 4, ',', '');
        $slope = $this->computeSlopeClass();
        $areaHTML = "<tr><td>Area:</td><td><strong>$area</strong></td></tr>";
        $slopeHTML = "<tr><td>Classe Pendenza:</td><td><strong>$slope</strong></td></tr>";
        $wayHTML = "<tr><td>Classe Trasporto:</td><td><strong>$this->way</strong></td></tr>";
        $municipalityHTML = "<tr><td>Comune:</td><td><strong>$this->municipality</strong></td></tr>";
        $detailsBlockHTML = "<h2><strong>Dettagli:</strong></h2><table><tbody>$areaHTML$slopeHTML$wayHTML$municipalityHTML</tbody></table>";
        $costsBlockHTML = "<h2><strong>Costi:</strong></h2><table><tbody><tr><td>Costo:</td><td><strong>$this->cost</strong></td></tr></tbody></table>";

        //if $this->catalog_estimate is an empty array return "nessun intervento", else return the table
        if (empty($this->catalog_estimate)) {
            $interventionsHTML = "<h1><strong>Nessun intervento</strong></h1>";
        } else {
            $interventionsHTML = "<h2><strong>Interventi:</strong></h2><table><thead><tr><th>Cod.</th><th>Descrizione</th><th>Area Di Intervento</th></tr></thead><tbody>";
            foreach ($this->catalog_estimate['interventions']['items'] as $intervention) {
                //find the name of the catalog_type that has the id equal to the first letter of $intervention['code']
                $catalog_type_description = DB::select("select name from catalog_types where cod_int = '" . substr($intervention['code'], 0, 1) . "';")[0]->name;
                $interventionsHTML .= "<tr><td>{$intervention['code']}</td><td>{$catalog_type_description}</td><td>{$intervention['area']}</td></tr>";
            }
            $interventionsHTML .= "</tbody></table>";
        }
        return [
            'id' => $this->id,
            'name' => [
                'it' => $this->code,
            ],
            'description' => [
                'it' => "$interventionsHTML$break$costsBlockHTML$break$detailsBlockHTML$break"
            ],
            'related_url' => [
                'https://sisteco.maphub.it/cadastral-parcels/' . $this->id,
            ],
            'geometry' => json_decode($geometry, true),

        ];
    }
}