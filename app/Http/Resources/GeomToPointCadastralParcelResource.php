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
        return [
            'id' => $this->id,
            'name' => [
                'it' => $this->code,
            ],
            'description' => [
                'it' => "<table>
	<tbody>
		<tr>
			<td><strong>Area:</strong></td>
			<td>$this->square_meter_surface</td>
		</tr>
		<tr>
			<td><strong>Classe Pendenza:</strong></td>
			<td>$this->slope</td>
		</tr>
		<tr>
			<td><strong>Pendenza:</strong></td>
			<td>$this->average_slope</td>
		</tr>
		<tr>
			<td><strong>Classe Trasporto:</strong></td>
			<td>$this->way</td>
		</tr>
		<tr>
			<td><strong>Comune:</strong></td>
			<td>$this->municipality</td>
		</tr>
		<tr>
		</tr>
	</tbody>
    <h3>Interventi Forestali:</h3>
<table>
	<thead>
		<tr>
			<th>Codice</th>
			<th>Costo Singolo</th>
			<th>Costo Complessivo</th>
		</tr>
	</thead>
</table>
</table>"
            ],
            'related_url' => [
                'https://sisteco.maphub.it/cadastral-parcels/' . $this->id,
            ],
            'geometry' => json_decode($geometry, true),

        ];
    }
}
