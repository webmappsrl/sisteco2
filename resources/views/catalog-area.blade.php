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
    <h2>Tipo intervento forestale: {{ $catalogArea->catalog_estimate['interventions']['info']['name'] }}</h2>
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
                <div><span style="background-color:rgba(128, 86, 52, 1);"></span> Avviamento ad alto fusto</div>
                <div><span style="background-color:rgba(219, 30, 210, 1);"></span> Recupero post Incendio</div>
                <div><span style="background-color:rgba(128, 255, 0, 1);"></span> Selvicoltura ad Albero</div>
            </div>
        </div>
        <div>
            <h3>{{ $catalogArea->catalog_estimate['interventions']['info']['excerpt'] }}</h3>
            <p>{{ $catalogArea->catalog_estimate['interventions']['info']['description'] }}</p>
        </div>
        <table class="parcel-details-table">
            <tbody>
                <tr>
                    <th>Stima</th>
                    <td><strong>
                        {{ number_format($catalogArea->estimated_value, 2, ',', '.') }} €
                    </strong></td>
                </tr>
                <tr>
                    <th>Superficie</th>
                    <td>{{ number_format($area_ha, 4, ',', '.') }} ha</td>
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
                        {{ number_format($catalogArea->streets_min_dist, 2, ',', '.') }} m /
                        {{ number_format($catalogArea->hiking_routes_min_dist, 2, ',', '.') }} m /
                        {{ $catalogArea->computeTransportClass() }}
                    </td>
                </tr>
                <tr>
                    <th>Sentieri presenti nell'area (metri / dettaglio)</th>
                    <td>
                        {{ number_format($catalogArea->hiking_routes_length, 0) }} m /
                        {{ $i['info']['hiking_routes_details'] }}
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
                        <td style="text-align: right;">{{ number_format($item['price'],2,',','.') }} €</td>
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
                        {{ number_format($i['info']['hiking_routes_total_cost'], 2, ',', '.') }}
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
                    <td>{{ number_format($i['info']['price'], 2, ',', '.') }}
                        €</td>
                </tr>
                <td style="background-color: white;">Costi accessori:</td>
                <tr>
                <tr>
                    <td style="text-align:center; background-color: white;">Impresa Forestale:</td>
                    <td style="background-color: white;">{{ number_format($i['info']['company_price'], 2, ',', '.') }} €
                    </td>
                </tr>
                <td style="text-align:center; background-color: white;">
                    Gestione e Certificazione</td>
                <td> {{ number_format($i['info']['certification_and_management_price'], 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td style="background-color:yellow;"><strong>Totale Costo Interventi Certificati Unità Funzionale
                        </strong></td>
                    <td style="background-color:yellow;">
                        <strong>{{ number_format($i['info']['total_net_price'], 2, ',', '.') }}
                            €</strong>
                    </td>
                </tr>

                </tr>
                <tr>
                    <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}
                        {{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }}</td>
                    <td style="font-size: 10px;">{{ number_format($i['info']['total_vat_price'], 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">Totale Con IVA</td>
                    <td style="font-size: 10px;">{{ number_format($i['info']['total_gross_price'], 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="background-color:yellow;">Costo / Ettaro Interventi Certificati</td>
                    <td style="background-color:yellow;">
                        {{ number_format($i['info']['total_net_price_per_area'],2,',','.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}
                        {{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }}</td>
                    <td style="font-size: 10px;">{{ number_format($i['info']['total_vat_price_per_area'],2,',','.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">Totale Con IVA</td>
                    <td style="font-size: 10px;">{{ number_format($i['info']['total_gross_price_per_area'],2,',','.') }}
                        €
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Manutenzione</h2>
        <table class="maintenance-table column-nd-text-right">
            <thead>
                <tr>
                    <th></th>
                    <th>Costo interventi</th>
                </tr>
            </thead>
            @foreach ($catalogArea->catalog_estimate['maintenance']['years'] as $index => $item)
                <tr>
                    <td>Anno {{ $index + 1 }}</td>
                    <td>{{ number_format($item['intervention_total_price'],2,',','.') }} €</td>
                </tr>
            @endforeach
            <tr>
                <th>Descrizione</th>
                <th>Totale</th>
            </tr>
            <tr>
                <td>Totale Interventi</td>
                <td>{{ number_format($m['summary']['intervention_total_price'],2,',','.')}} €</td>
            </tr>
            <tr>
                <td>Impresa forestale</td>
                <td>{{ number_format($m['summary']['company_price'],2,',','.')}} €</td>
            </tr>
            <tr>
                <td>Gestione e certificazioni</td>
                <td>{{ number_format($m['summary']['certification_and_management_price'],2,',','.')}} €</td>
            </tr>
            <tr>
                <td style="background-color:yellow;"><strong>Totale Costo Mantenimento Certificato</strong></td>
                <td style="background-color:yellow;">
                    <strong>{{ number_format($catalogArea->catalog_estimate['maintenance']['summary']['total_net_price'],2,',','.') }}
                        €</strong>
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}</td>
                <td style="font-size: 10px;">
                    {{ number_format($catalogArea->catalog_estimate['maintenance']['summary']['total_vat'],2,',','.') }}                    €</td>
            </tr>
            <tr>
                <td style="font-size: 10px;">Totale con IVA</td>
                <td style="font-size: 10px;">
                    {{ number_format($catalogArea->catalog_estimate['maintenance']['summary']['total_gross_price'],2,',','.') }}                    € </td>
            </tr>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Totale generale</h2>
        <table class="total-table column-nd-text-right">
            <tbody style="background-color: #B3C6E7;">
                <tr>
                    <th style="background-color: #B3C6E7;">Totale Generale interventi + mantenimento</th>
                    <td style="background-color: #B3C6E7;">
                        <strong>{{ number_format($g['total_net_price'],2,',','.') }}</strong>
                        € </td>
                </tr>
                <tr style="font-size:10px;">
                    <th style="background-color: #B3C6E7;">IVA</th>
                    <td style="background-color: #B3C6E7;">
                        {{ number_format($g['total_vat_price'],2,',','.') }}  
                </tr>
                <tr style="font-size:10px;">
                    <th style="background-color: #B3C6E7;">Totale Generale con IVA</th>
                    <td style="background-color: #B3C6E7;">
                        {{ number_format($g['total_gross_price'],2,',','.') }}   
                        €
                    </td>
                </tr>
                <tr style="border: none; background-color:#B3C6E7;">
                    {{-- <th>Totale Netto</th>
                    <td>{{ $catalogArea->catalog_estimate['general']['total_net_price'] }} €</td> --}}
                    <td style="border: none;"> </td>
                    <td style="border: none;"></td>
                </tr>
                <tr>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>

                </tr>
                <tr>
                    <th style="background-color: #B3C6E7;">Costo Generale / Ettaro Interventi + Mantenimento</th>
                    <td style="background-color: #B3C6E7;">
                        {{ number_format($g['total_net_price_per_area'], 2, ',', '.') }} €</td>
                </tr>
                <tr style="font-size:10px;">
                    <th style="background-color: #B3C6E7;">IVA</th>
                    <td>{{ number_format($g['total_vat_price_per_area'], 2, ',', '.') }} 
                        €
                    </td>
                </tr>
                <tr style="font-size:10px;">
                    <th style="background-color: #B3C6E7;">Costo totale generale/ettaro con IVA</th>
                    <td style="background-color: #B3C6E7;">{{ number_format($g['total_gross_price_per_area'], 2, ',', '.') }} 
                    </td>
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
