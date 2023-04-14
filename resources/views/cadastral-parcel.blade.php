<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/app.css">
    <title>Parcella n.{{ $cadastralParcel->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <base href="/cadastral-parcels/{{ $cadastralParcel->id }}">
</head>

<body>
    <h1>Dettagli della Particella Catastale {{ $cadastralParcel->code }}</h1>
    <div class="parcel-details">
        <div class="map-container">
            <app-root parcel="{{ $cadastralParcel->id }}"></app-root>
        </div>
        <table class="parcel-details-table">
            <tbody>
                <tr>
                    <th>Comune</th>
                    <td>{{ $cadastralParcel->municipality }}</td>
                </tr>
                <tr>
                    <th>Stima</th>
                    <td>{{ number_format($cadastralParcel->estimated_value, 2, ',', '.') }}€</td>
                </tr>
                <tr>
                    <th>Pendenza</th>
                    <td>{{ number_format($cadastralParcel->average_slope,2,',','.') }} ({{$cadastralParcel->computeSlopeClass()}})</td>
                </tr>
                <tr>
                    <th>Trasporti</th>
                    <td>{{ $cadastralParcel->meter_min_distance_road }} m / {{ $cadastralParcel->meter_min_distance_path }} m ({{$cadastralParcel->computeTransportClass()}})</td>
                </tr>
                <tr>
                    <th>Superficie</th>
                    <td>{{ number_format($cadastralParcel->square_meter_surface/10000, 2, ',', '.') }} ha</td>
                </tr>
            </tbody>
        </table>
    </div>
    <hr>
    <div class="pagebreak"> </div>
    @if (!empty($cadastralParcel->catalog_estimate))
        <h2>Interventi Forestali</h2>
        <table class="interventions-table">
            <thead>
                <tr>
                    <th>Codice Intervento</th>
                    <th>Area</th>
                    <th>Costo €/Ettaro</th>
                    <th>Totale Incl. IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cadastralParcel->catalog_estimate['interventions']['items'] as $item)
                    <tr>
                        <td>{{ $item['code'] }} ({{DB::select("select name from catalog_types where cod_int = '" . substr($item['code'], 0, 1) . "';")[0]->name;}})</td>
                        <td>{{ $item['area'] }}</td>
                        <td>{{ $item['unit_price'] }}€</td>
                        <td>{{ $item['price'] }}€</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <table class="interventions-table">
            <thead>
                <tr>
                    <th>Descrizione</th>
                    <th>Totale Incl. IVA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Costo Interventi (Somma costi interventi)</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['intervention_price'] }}€</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['supervision']['label'] }} ({{ $sisteco['supervision']['value'] . $sisteco['supervision']['unit'] }} di costi interventi)</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['supervision_price'] }}€</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['overheads']['label'] }} ({{ $sisteco['overheads']['value'] . $sisteco['overheads']['unit'] }} di costo interventi)</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['overhead_price'] }}€</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['business_profit']['label'] }} ({{ $sisteco['business_profit']['value'] . $sisteco['business_profit']['unit'] }} di costo interventi)</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['business_profit_price'] }}€
                    </td>
                </tr>
                <tr>
                    <td>{{ $sisteco['intervention_certification']['label'] }}</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['intervention_certification'] }}€
                    </td>
                </tr>
                <tr>
                    <td><strong>Totale Interventi certificati </strong></td>
                    <td><strong>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_certificated_price'] }}€</strong>
                    </td>
                </tr>
                <tr>
                    <td>{{ $sisteco['team_management']['label'] }} ({{ $sisteco['team_management']['value'] . $sisteco['team_management']['unit'] }} di totale interventi certificati)</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['team_price'] }}</td>
                </tr>
                <tr>
                    <td>{{ $sisteco['platform_maintenance']['label'] }} ({{ $sisteco['platform_maintenance']['value'] . $sisteco['platform_maintenance']['unit'] }} di totale interventi certificati)</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['platform_maintenance_price'] }}
                    </td>
                </tr>
                <tr>
                    <td> <strong>Totale Lordo Interventi</strong> </td>
                    <td><strong>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_gross_price'] }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>{{ $sisteco['vat']['label'] }} ({{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }})</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_vat'] }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Totale Netto Interventi</strong></td>
                    <td><strong>{{ $cadastralParcel->catalog_estimate['interventions']['info']['total_intervention_net_price'] }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>Costo Lordo/Ettaro</td>
                    <td>{{ $cadastralParcel->catalog_estimate['interventions']['info']['intervention_gross_price_per_area'] }}
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Mantenimento</h2>
        <p style="text-align: center;">
            Area Interventi: {{$cadastralParcel->catalog_estimate['interventions']['info']['intervention_area']}} ha
            <br>
            Costo Manutenzione {{$sisteco['maintenance']['unit']}} {{$sisteco['maintenance']['value']}}
        </p>
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>Codice Intervento</th>
                    <th>Totale Incl. IVA</th>
                </tr>
            </thead>
            @foreach ($cadastralParcel->catalog_estimate['maintenance']['items'] as $index => $item)
                <tr>
                    <td>Mantenimento anno {{ $index + 1 }}</td>
                    <td>{{ $item['price'] }}€</td>
                </tr>
            @endforeach
            <tr>
                <td>Certificazione 2° anno</td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['certifications'][0]['price'] }}€</td>
            </tr>
            <tr>
                <td>Certificazione 5° anno</td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['certifications'][1]['price'] }}€</td>
            </tr>
            <tr>
                <td><strong>Totale Lordo Manutenzioni</strong></td>
                <td><strong>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['total_maintenance_gross_price'] }}€</strong>
                </td>
            </tr>
            <tr>
                <td>{{ $sisteco['vat']['label'] }}</td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['total_maintenance_vat'] }}€</td>
            </tr>
            <tr>
                <td><strong>Totale Netto Manutenzioni</strong></td>
                <td><strong>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['total_maintenance_net_price'] }}€</strong>
                </td>
            </tr>
            <tr>
                <td>Costo Lordo/Ettaro</td>
                <td>{{ $cadastralParcel->catalog_estimate['maintenance']['summary']['maintenance_gross_price_per_area'] }}€
                </td>
            </tr>
        </table>
        <hr>
        <h2>Totale generale</h2>
        <table class="total-table">
            <tbody>
                <tr>
                    <th>Totale Lordo</th>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_gross_price'] }}€</td>
                </tr>
                <tr>
                    <th>IVA 22%</th>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_vat'] }}</td>
                </tr>
                <tr>
                    <th>Totale Netto</th>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_net_price'] }}€</td>
                </tr>
                <tr>
                    <th>Totale Lordo/Ettaro</th>
                    <td>{{ $cadastralParcel->catalog_estimate['general']['total_gross_price_per_area'] }}€</td>
                </tr>
            </tbody>
        @else
            <div class="message">
                <p>Per questa particella non sono previsti interventi forestali</p>
            </div>

    @endif

    <script src="{{ asset('js/runtime.js') }}" defer></script>
    <script src="{{ asset('js/polyfills.js') }}" defer></script>
    <script src="{{ asset('js/main.js') }}" defer></script>
</body>

</html>
