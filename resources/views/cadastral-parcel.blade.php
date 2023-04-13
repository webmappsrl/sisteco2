<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/app.css">
    <title>Parcella n.{{ $cadastralParcel->id }}</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>
    <div class="parcel-details">
        <h1>Dettagli della Parcella</h1>
        <app-map parcel="{{ $cadastralParcel->id }}"></app-map>

        <table class="parcel-details-table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Codice</th>
                    <th>Località</th>
                    <th>Valore Stimato</th>
                    <th>Pendenza</th>
                    <th>Distanza Strada</th>
                    <th>Distanza Sentiero</th>
                    <th>Superficie</th>
                    <th>Pendenza</th>
                    <th>Strada</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $cadastralParcel->id }}</td>
                    <td>{{ $cadastralParcel->code }}</td>
                    <td>{{ $cadastralParcel->municipality }}</td>
                    <td>{{ number_format($cadastralParcel->estimated_value, 2, ',', '.') }}€</td>
                    <td>{{ $cadastralParcel->average_slope }}</td>
                    <td>{{ $cadastralParcel->meter_min_distance_road }} m </td>
                    <td>{{ $cadastralParcel->meter_min_distance_path }} m </td>
                    <td>{{ number_format($cadastralParcel->square_meter_surface, 2, ',', '.') }} m²</td>
                    <td>{{ $cadastralParcel->slope }}</td>
                    <td>{{ $cadastralParcel->way }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <hr>
    @if (!empty($cadastralParcel->catalog_estimate))
        <table class="interventions-table">
            <thead>
                <tr>
                    <th>Interventi Forestali </th>
                    <th>Codice Intervento</th>
                    <th>Area</th>
                    <th>Costo €/Ettaro</th>
                    <th>Totale Incl. IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cadastralParcel->catalog_estimate['interventions']['items'] as $item)
                    <tr>
                        <td></td>
                        <td>{{ $item['code'] }}</td>
                        <td>{{ $item['area'] }}</td>
                        <td>{{ $item['unit_price'] }}€</td>
                        <td>{{ $item['price'] }}€</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Costo Interventi</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['intervention_price'] }}€</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['supervision']['label'] }}</td>
                    <td>{{ $sisteco['supervision']['value'] . $sisteco['supervision']['unit'] }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['supervision_price'] }}€</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['overheads']['label'] }}</td>
                    <td>{{ $sisteco['overheads']['value'] . $sisteco['overheads']['unit'] }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['overhead_price'] }}€</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['business_profit']['label'] }}</td>
                    <td>{{ $sisteco['business_profit']['value'] . $sisteco['business_profit']['unit'] }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['business_profit_price'] }}€
                    </td>
                </tr>
                <tr>
                    <td>{{ $sisteco['intervention_certification']['label'] }}</td>
                    <td>
                    </td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['intervention_certification'] }}€
                    </td>
                </tr>
                <tr>
                    <td><strong>Totale Interventi certificati </strong></td>
                    <td>
                    </td>
                    <td></td>
                    <td></td>
                    <td><strong>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_certificated_price'] }}€</strong>
                    </td>
                </tr>
                <tr>
                    <td>{{ $sisteco['team_management']['label'] }}</td>
                    <td>{{ $sisteco['team_management']['value'] . $sisteco['team_management']['unit'] }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['team_price'] }}</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['platform_maintenance']['label'] }}</td>
                    <td>{{ $sisteco['platform_maintenance']['value'] . $sisteco['platform_maintenance']['unit'] }}
                    </td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['platform_maintenance_price'] }}
                    </td>
                </tr>
                <tr>
                    <td> <strong>Totale Lordo Interventi</strong> </td>
                    <td>
                    </td>
                    <td></td>
                    <td></td>
                    <td><strong>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_gross_price'] }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>{{ $sisteco['vat']['label'] }}</td>
                    <td>{{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_vat'] }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Totale Netto Interventi</strong></td>
                    <td>
                    </td>
                    <td></td>
                    <td></td>
                    <td><strong>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_net_price'] }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>Costo Lordo/Ettaro</td>
                    <td>
                    </td>
                    <td></td>
                    <td></td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['intervention_gross_price_per_area'] }}
                    </td>
                </tr>

            </tbody>
        </table>
        <hr>
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>Mantenimento </th>
                    <th>Codice Intervento</th>
                    <th>Area</th>
                    <th>Costo €/Ettaro</th>
                    <th>Totale Incl. IVA</th>
                </tr>
            </thead>
            @foreach ($cadastralParcel->catalog_estimate['maintenance']['items'] as $index => $item)
                <tr>
                    <td></td>
                    <td>Mantenimento anno {{ $index + 1 }}</td>
                    <td>{{ $item['area'] }}</td>
                    <td>{{ $item['unit_price'] }}€</td>
                    <td>{{ $item['price'] }}€</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td>Certificazione 2° anno</td>
                <td></td>
                <td></td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['certifications'][0]['price'] }}€</td>
            </tr>
            <tr>
                <td></td>
                <td>Certificazione 5° anno</td>
                <td></td>
                <td></td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['certifications'][1]['price'] }}€</td>
            </tr>
            <tr>
                <td></td>
                <td><strong>Totale Lordo Manutenzioni</strong></td>
                <td></td>
                <td></td>
                <td><strong>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['total_maintenance_gross_price'] }}€</strong>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>{{ $sisteco['vat']['label'] }}</td>
                <td></td>
                <td></td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['total_maintenance_vat'] }}€</td>
            </tr>
            <tr>
                <td></td>
                <td><strong>Totale Netto Manutenzioni</strong></td>
                <td></td>
                <td></td>
                <td><strong>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['total_maintenance_net_price'] }}€</strong>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>Costo Lordo/Ettaro</td>
                <td></td>
                <td></td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['maintenance_gross_price_per_area'] }}€
                </td>
            </tr>
        </table>
        <hr>
        <table class="total-table">
            <thead>
                <tr>
                    <th>Totale Lordo</th>
                    <th>IVA 22%</th>
                    <th>Totale Netto</th>
                    <th>Totale Lordo/Ettaro</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_gross_price'] }}€</td>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_vat'] }}</td>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_net_price'] }}€</td>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_gross_price_per_area'] }}€</td>
                </tr>
            </tbody>
        @else
            <div class="message">
                <p>Per questa particella non sono previsti interventi forestali</p>
            </div>

    @endif












    <script src="runtime.js" defer></script>
    <script src="polyfills.js" defer></script>
    <script src="main.js" defer></script>
</body>

</html>
