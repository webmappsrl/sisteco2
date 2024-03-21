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

        //define the details block variables
        $area = number_format($this->square_meter_surface / 10000, 4, ',', '');
        $slope = $this->computeSlopeClass();
        $areaHTML = "<tr><td>Area:</td><td><strong>$area ha</strong></td></tr>";
        $slopeHTML = "<tr><td>Classe Pendenza:</td><td><strong>$slope</strong></td></tr>";
        $wayHTML = "<tr><td>Classe Trasporto:</td><td><strong>$this->way</strong></td></tr>";
        $municipalityHTML = "<tr><td>Comune:</td><td><strong>$this->municipality</strong></td></tr>";
        $detailsBlockHTML = "<h2><strong>Dettagli:</strong></h2><table><tbody>$areaHTML$slopeHTML$wayHTML$municipalityHTML</tbody></table>";
        $interventionsBlockHTML = "";
        $costsBlockHTML = "";
        $button = "<button>Sostieni</button>";

        if (empty($this->catalog_estimate)) {
            $interventionsBlockHTML = "<h1><strong>Nessun intervento</strong></h1>";
            $costsBlockHTML = "<h1><strong>Nessun costo</strong></h1>";
        }
        //if $this->catalog_estimate is an empty array return "nessun intervento", else return the table
        else {
            $interventiNonFormatted = str_replace('.', '', $this->catalog_estimate['interventions']['info']['intervention_price']);
            $interventiNonFormatted2 = str_replace(',', '.', $interventiNonFormatted);
            $lordoInterventinonFormatted = str_replace('.', '', $this->catalog_estimate['interventions']['info']['total_intervention_gross_price']);
            $lordoInterventiNonFormatted2 = str_replace(',', '.', $lordoInterventinonFormatted);
            $costoInterventi = floatval($interventiNonFormatted2);
            $costoLordoInterventi = floatval($lordoInterventiNonFormatted2);
            $costiAccessori = $costoLordoInterventi - $costoInterventi;
            $costiAccessori = number_format($costiAccessori, 2, ',', '.');
            $costoInterventi = number_format($interventiNonFormatted2, 2, ',', '.');
            $costoLordoAnno = $this->catalog_estimate['interventions']['info']['total_intervention_gross_price'];
            $lordoManutenzione = $this->catalog_estimate['maintenance']['summary']['total_maintenance_gross_price'];
            $totaleGenerale =  $this->catalog_estimate['general']['total_gross_price'];

            //define the interventions block 
            $interventionsBlockHTML = "<h2><strong>Interventi:</strong></h2><table><thead><tr><th>Cod.</th><th>Descrizione</th><th>Area Di Intervento</th></tr></thead><tbody>";
            foreach ($this->catalog_estimate['interventions']['items'] as $intervention) {
                //find the name of the catalog_type that has the id equal to the first letter of $intervention['code']
                $catalog_type_description = DB::select("select name from catalog_types where cod_int = '" . substr($intervention['code'], 0, 1) . "';")[0]->name;
                $interventionsBlockHTML .= "<tr><td>{$intervention['code']}</td><td>{$catalog_type_description}</td><td>{$intervention['area']} ha</td></tr>";
            }
            $interventionsBlockHTML .= "</tbody></table>";
            $costsBlockHTML = "<h2><strong>Costi:</strong></h2><table><thead><tr><th>Voce</th><th>Costo</th></tr></thead><tbody>";
            $costsBlockHTML .= "<tr><td>Costo interventi</td><td>{$costoInterventi} €</td></tr>";
            $costsBlockHTML .= "<tr><td>Costi accessori</td><td>{$costiAccessori} €</td></tr>";
            $costsBlockHTML .= "<tr><td>Costo lordo anno 0</td><td>{$costoLordoAnno} €</td></tr>";
            $costsBlockHTML .= "<tr><td>Lordo manutenzione</td><td>{$lordoManutenzione} €</td></tr>";
            $costsBlockHTML .= "<tr><td>Totale generale</td><td>{$totaleGenerale} €</td></tr>";
            $costsBlockHTML .= "</tbody></table>";
        }

        return [
            'id' => $this->id,
            'name' => [
                'it' => $this->code,
            ],
            'description' => [
                'it' => "$interventionsBlockHTML$break$costsBlockHTML$break$detailsBlockHTML$break$button"
            ],
            'related_url' => [
                ' https://sis-te.com/cadastral-parcels/' . $this->id,
            ],
            'geometry' => json_decode($geometry, true),

        ];
    }
}