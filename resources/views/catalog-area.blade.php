<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/app.css">
    <title>Area n.{{ $catalogArea->id }}</title>
    <link rel="stylesheet"
        href="https://cdn.statically.io/gh/webmappsrl/feature-collection-widget-map/8778f562/dist/styles.css">
    <base href="/catalog-areas/{{ $catalogArea->id }}">
</head>

<body>
    <h1>Dettagli dell'area {{ $catalogArea->id }}</h1>
    <div class="parcel-details">
        <div class="map-container">
            <feature-collection-widget-map geojsonurl="{{ url('api/v1/geom/catalogarea/' . $catalogArea->id) }}">
            </feature-collection-widget-map>

        </div>
        <div class="legenda">
            <div class="color-plate">
                <div><span style="background-color:rgba(191, 191, 191, 1);"></span> Nessuna Lavorazione</div>
                <div><span style="background-color:rgba(255, 221, 1, 1);"></span> Diradamento</div>
                <div><span style="background-color:rgba(255, 1, 14, 1);"></span> Taglio ceduo</div>
                <div><span style="background-color:rgba(128, 86, 52, 1);"></span> Avviamento</div>
                <div><span style="background-color:rgba(219, 30, 210, 1);"></span> Recupero post Incendio</div>
                <div><span style="background-color:rgba(128, 255, 0, 1);"></span> Selvicoltura ad Albero</div>
            </div>
        </div>
        <table class="parcel-details-table">
            <tbody>
                <tr>
                    <th>Stima</th>
                    <td>{{ number_format($catalogArea->estimated_value, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <th>Superficie</th>
                    <td>{{ number_format($area / 10000, 4, ',', '.') }} ha</td>
                </tr>
                <tr>
                    <th>Pendenza (min / avg / max / classe)</th>
                    <td>
                        {{ number_format($catalogArea->slope_min, 2, ',', '.') }} deg /
                        {{ number_format($catalogArea->slope_avg, 2, ',', '.') }} deg /
                        {{ number_format($catalogArea->slope_max, 2, ',', '.') }} deg /
                        {{ $catalogArea->slope_class }}
                    </td>
                </tr>
                <tr>
                    <th>Trasporto (strade / sentieri / classe)</th>
                    <td>
                        {{ number_format($catalogArea->hiking_routes_min_dist, 2, ',', '.') }} m /
                        X /
                        X
                    </td>
                </tr>
                <tr>
                    <th>Sentieri presenti nell'area (metri / dettaglio)</th>
                    <td>
                        {{ number_format($catalogArea->hiking_routes_length, 0) }} m /
                        {{ $hiking_routes_details_string }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <hr>
    <div class="pagebreak"> </div>
    @if (!empty($catalogArea->catalog_estimate))
        <h2>Interventi Forestali</h2>
        <table class="interventions-table">
            <thead>
                <tr>
                    <th>Codice Intervento</th>
                    <th>Area</th>
                    <th>Costo €/Ettaro</th>
                    <th style="text-align: right;">Totale</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($catalogArea->catalog_estimate['interventions']['items'] as $item)
                    <tr>
                        <td>{{ $item['code'] }}
                            ({{ DB::select("select name from catalog_types where cod_int = '" . substr($item['code'], 0, 1) . "';")[0]->name }})
                        </td>
                        <td>{{ $item['area'] }}</td>
                        <td>{{ $item['unit_price'] }} €</td>
                        <td style="text-align: right;">{{ $item['price'] }} €</td>
                    </tr>
                @endforeach
            </tbody>
            <thead>
                <tr>
                    <th></th>
                    <th>Km</th>
                    <th>Costo €/Km</th>
                    <th style="text-align: right;">Totale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sentieristica</td>
                    <td style="background-color: greenyellow;">
                        {{ number_format(round($catalogArea->hiking_routes_length, 2) / 1000, 2, ',', '.') }}</td>
                    <td>{{ number_format($sisteco['hiking_routes_cost_per_km']['value'], 2, ',', '.') }}€
                    </td>
                    <td style="text-align: right;">
                        {{ number_format(round($hikingRoutesTotalCost, 2), 2, ',', '.') }}
                        €
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="interventions-table column-nd-text-right">
            <thead>
                <tr>
                    <th>Descrizione</th>
                    <th>Totale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Costo Interventi (Somma costi interventi)</td>
                    <td>{{ number_format($interventionPrice + $hikingRoutesTotalCost, 2, ',', '.') }}
                        €</td>
                </tr>
                <td>Costi accessori:</td>
                <tr>
                <tr>
                    <td style="text-align:center; background-color: white;">Impresa Forestale:</td>
                    <td>{{ number_format($forestalInterventionPrice, 2, ',', '.') }} €</td>
                </tr>
                <td style="text-align:center; background-color: white;">
                    Gestione e Certificazione</td>
                <td>{{ number_format($certificationAndManagement, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td style="background-color:yellow;"><strong>Totale Costo Interventi Certificati Unità Funzionale
                        </strong></td>
                    <td style="background-color:yellow;">
                        <strong>{{ number_format($totalNetCostFunctionalUnit, 2, ',', '.') }}
                            €</strong>
                    </td>
                </tr>

                </tr>
                <tr>
                    <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}
                        {{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }}</td>
                    <td style="font-size: 10px;">{{ number_format($vatFunctionalUnit, 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">Totale Con IVA</td>
                    <td style="font-size: 10px;">{{ number_format($totalCostFunctionalUnit, 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="background-color:yellow;">Costo / Ettaro Interventi Certificati</td>
                    <td style="background-color:yellow;">
                        {{ $catalogArea->catalog_estimate['interventions']['info']['intervention_gross_price_per_area'] }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}
                        {{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }}</td>
                    <td style="font-size: 10px;">{{ number_format($vatHectares, 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">Totale Con IVA</td>
                    <td style="font-size: 10px;">{{ number_format($totalHectaresCost, 2, ',', '.') }}
                        €
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Mantenimento</h2>
        <p style="text-align: center;">
            Area Interventi: {{ $catalogArea->catalog_estimate['interventions']['info']['intervention_area'] }} ha
            <br>
            Costo Manutenzione {{ $sisteco['maintenance']['unit'] }}
            {{ number_format($sisteco['maintenance']['value'], 2, ',', '.') }}
        </p>
        <table class="maintenance-table column-nd-text-right">
            <thead>
                <tr>
                    <th>Codice Intervento</th>
                    <th>Totale Incl. IVA</th>
                </tr>
            </thead>
            @foreach ($catalogArea->catalog_estimate['maintenance']['items'] as $index => $item)
                <tr>
                    <td>Mantenimento anno {{ $index + 1 }}</td>
                    <td>{{ $item['price'] }} €</td>
                </tr>
            @endforeach
            <tr>
                <td>Certificazione 2° anno</td>
                <td>{{ $catalogArea->catalog_estimate['maintenance']['certifications'][0]['price'] }} €</td>
            </tr>
            <tr>
                <td>Certificazione 5° anno</td>
                <td>{{ $catalogArea->catalog_estimate['maintenance']['certifications'][1]['price'] }} €</td>
            </tr>
            <tr>
                <td><strong>Totale Lordo Manutenzioni</strong></td>
                <td><strong>{{ $catalogArea->catalog_estimate['maintenance']['summary']['total_maintenance_gross_price'] }}
                        €</strong>
                </td>
            </tr>
            <tr>
                <td>{{ $sisteco['vat']['label'] }}</td>
                <td>{{ $catalogArea->catalog_estimate['maintenance']['summary']['total_maintenance_vat'] }} €</td>
            </tr>
            <tr>
                <td><strong>Totale Netto Manutenzioni</strong></td>
                <td><strong>{{ $catalogArea->catalog_estimate['maintenance']['summary']['total_maintenance_net_price'] }}
                        €</strong>
                </td>
            </tr>
            <tr>
                <td>Costo Lordo/Ettaro</td>
                <td>{{ $catalogArea->catalog_estimate['maintenance']['summary']['maintenance_gross_price_per_area'] }}
                    €
                </td>
            </tr>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Totale generale</h2>
        <table class="total-table column-nd-text-right">
            <tbody>
                <tr>
                    <th>Totale Lordo</th>
                    <td>{{ $catalogArea->catalog_estimate['general']['total_gross_price'] }} €</td>
                </tr>
                <tr>
                    <th>IVA 22%</th>
                    <td>{{ $catalogArea->catalog_estimate['general']['total_vat'] }} €</td>
                </tr>
                <tr>
                    <th>Totale Netto</th>
                    <td>{{ $catalogArea->catalog_estimate['general']['total_net_price'] }} €</td>
                </tr>
                <tr>
                    <th>Totale Lordo/Ettaro</th>
                    <td>{{ $catalogArea->catalog_estimate['general']['total_gross_price_per_area'] }} €</td>
                </tr>
            </tbody>
        @else
            <div class="message">
                <p>Per questa area non sono previsti interventi forestali</p>
            </div>

    @endif

    <script src="https://cdn.statically.io/gh/webmappsrl/feature-collection-widget-map/8778f562/dist/runtime.js" defer>
    </script>
    <script src="https://cdn.statically.io/gh/webmappsrl/feature-collection-widget-map/8778f562/dist/polyfills.js" defer>
    </script>
    <script src="https://cdn.statically.io/gh/webmappsrl/feature-collection-widget-map/8778f562/dist/main.js" defer>
    </script>
</body>

</html>
