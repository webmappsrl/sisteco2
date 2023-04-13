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

        $interventions = "<h2><strong>Interventi:</strong></h2><table><thead><tr><th>Cod.</th><th>P.Unit.</th><th>P. Tot</th></tr></thead><tbody>";
        $area = "<tr><td>Area:</td><td><strong>$this->square_meter_surface</strong>m²</td></tr>";
        $slope = "<tr><td>Classe Pendenza:</td><td><strong>$this->slope</strong></td></tr>";
        $way = "<tr><td>Classe Trasporto:</td><td><strong>$this->way</strong></td></tr>";
        $municipality = "<tr><td>Comune:</td><td><strong>$this->municipality</strong></td></tr>";

        //if $this->catalog_estimate is an empty array return "nessun intervento", else return the table
        if (empty($this->catalog_estimate)) {
            $interventions = "<p><strong>Nessun intervento</strong></p>";
        } else {
            foreach ($this->catalog_estimate['interventions']['items'] as $intervention) {
                $interventions .= "<tr><td>{$intervention['code']}</td><td>{$intervention['unit_price']}€</td><td>{$intervention['price']}€</td></tr>";
            }
            $interventions .= "</tbody></table>";
        }

        // //if $intervention is an empty string return "nessun intervento", else return the table
        // $interventions = $interventions == "" ? "<tr><td><strong>Nessun intervento</strong></td></tr>" : $interventions;
        //if $area is an empty string return "/", else return the table
        $area = $area == "" ? "<tr><td><strong>/</strong></td></tr>" : $area;
        //if $slope is an empty string return "/", else return the table
        $slope = $slope == "" ? "<tr><td><strong>/</strong></td></tr>" : $slope;
        //if $way is an empty string return "/", else return the table
        $way = $way == "" ? "<tr><td><strong>/</strong></td></tr>" : $way;
        //if $municipality is an empty string return "/", else return the table
        $municipality = $municipality == "" ? "<tr><td><strong>/</strong></td></tr>" : $municipality;

        return [
            'id' => $this->id,
            'name' => [
                'it' => $this->code,
            ],
            'description' => [
                'it' => "$interventions<br><br><strong>Dettagli:</strong></h2><table><tbody>$area$slope$way$municipality</tbody></table>"
            ],
            'related_url' => [
                'https://sisteco.maphub.it/cadastral-parcels/' . $this->id,
            ],
            'geometry' => json_decode($geometry, true),

        ];
    }
}