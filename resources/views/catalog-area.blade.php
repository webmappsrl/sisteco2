@php
    $featuredImage = $catalogArea->getFirstMedia('gallery');
@endphp

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
    <div class="header d-flex justify-content-center align-items-center bg-light text-white py-3 fixed-top mb-4 ">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#supportProjectModal">
            Sostieni il progetto
        </button>
    </div>
    <div class="modal fade" id="supportProjectModal" tabindex="-1" aria-labelledby="supportProjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supportProjectModalLabel">BOSCHI, BENE COMUNE: CONTRIBUISCI ALLA
                        TUTELA
                        E ALLA GESTIONE DEL PATRIMONIO NATURALE DEL MONTE PISANO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fst-italic fw-light ">Boschi mantenuti secondo le regole di una corretta gestione
                        forestale, attiva
                        e
                        responsabile,
                        favoriscono il miglioramento della qualità dell’aria, contribuiscono alla stabilizzazione
                        dei
                        versanti, alla riduzione del rischio idrogeologico e del rischio incendi, concorrono alla
                        mitigazione dei cambiamenti climatici.</p>

                    <p class="fst-italic fw-light "> Benefici ecosistemici dei quali l’intera collettività trae
                        vantaggio e del
                        cui
                        raggiungimento la
                        stessa collettività deve farsi carico.</p>
                    <form method="POST" action="{{ route('support.project') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="hidden" name="catalog-id" id="catalog-id"
                                value="{{ $catalogArea->catalog->id }}">
                            <input type="hidden" name="catalogArea-id" id="catalogArea-id"
                                value="{{ $catalogArea->id }}">
                            <label for="nome" class="form-label">Nome*</label>
                            <input type="text" class="form-control" name="nome" id="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="cognome" class="form-label">Cognome*</label>
                            <input type="text" class="form-control" name="cognome" id="cognome" required>
                        </div>
                        <div class="mb-3">
                            <label for="azienda" class="form-label">Azienda*</label>
                            <input type="text" class="form-control" name="azienda" id="azienda" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="tel" class="form-control" name="telefono" id="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="mb-3">
                            <p>Note</p>
                            <textarea name="note" id="note" cols="52" rows="5"></textarea>
                        </div>
                        <div>
                            <p class=" fw-lighter text-danger ">I campi contrassegnati con (*) sono obbligatori</p>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                            <button type="submit" class="btn btn-success">Invia</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @if (session()->has('Success'))
        <div class="alert alert-success mt-5 " id="success-alert">
            {{ session()->get('Success') }}
        </div>
    @endif

    @if (session()->has('Error'))
        <div class="alert alert-danger mt-5 " id="error-alert">
            {{ session()->get('Error') }}
        </div>
    @endif
    
    <h2 class=" mt-5 ">Gestione forestale attiva e responsabile boschi del Monte Pisano</h2>
    <h1>Dettagli dell'area {{ $catalogArea->id }}</h1>
    <h2>Tipo intervento forestale: {{ $catalogArea->catalog_estimate['interventions']['info']['name'] }}</h2>
    @if ($catalogArea->work_start_date)
        <p>Anno inizio lavoro: <strong>{{$catalogArea->work_start_date}}</strong></p>
    @endif
    @if ($featuredImage)
        <div class="parcel-details">
            <a href="{{ $featuredImage->getUrl() }}" target="_blank">
                {{ $featuredImage->img()->attributes(['class' => 'feature-image']) }}
            </a>
        </div>
    @endif
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
            @if ($catalogArea->catalog_estimate['interventions']['info']['excerpt'])
                <h3>{{ $catalogArea->catalog_estimate['interventions']['info']['excerpt'] }}</h3>
                <p>{{ $catalogArea->catalog_estimate['interventions']['info']['description'] }}</p>
            @endif
            @if ($catalogArea->catalogType->generated_ecosystem_servicesal)
                <h3>Servizi Ecosistemici Generati</h3>
                <p>{{ $catalogArea->catalogType->generated_ecosystem_servicesal }}</p>
            @endif
            <p style="font-style: italic">Per tutti i dettagli si rimanda al Piano di Gestione Forestale</p>
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
    <h2>Gestione forestale attiva e responsabile boschi del Monte Pisano</h2>

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
                        <td style="text-align: right;">{{ number_format($item['price'], 2, ',', '.') }} €</td>
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
                    <td style="text-align:left; background-color: white;">Oneri accessori impresa forestale:</td>
                    <td style="background-color: white;">
                        {{ number_format($i['info']['company_price'], 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <td style="text-align:left; background-color: white;">
                    Gestione e Certificazione</td>
                <td> {{ number_format($i['info']['certification_and_management_price'], 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td style="background-color:yellow;"><strong>Totale Costo Interventi Certificati Unità
                            Funzionale
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
                        {{ number_format($i['info']['total_net_price_per_area'], 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}
                        {{ $sisteco['vat']['value'] . $sisteco['vat']['unit'] }}</td>
                    <td style="font-size: 10px;">
                        {{ number_format($i['info']['total_vat_price_per_area'], 2, ',', '.') }}
                        €
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px;">Totale Con IVA</td>
                    <td style="font-size: 10px;">
                        {{ number_format($i['info']['total_gross_price_per_area'], 2, ',', '.') }}
                        €
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Gestione forestale attiva e responsabile boschi del Monte Pisano</h2>

        <h2>Mantenimento</h2>
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
                    <td>{{ number_format($item['intervention_total_price'], 2, ',', '.') }} €</td>
                </tr>
            @endforeach
            <tr>
                <th>Descrizione</th>
                <th>Totale</th>
            </tr>
            <tr>
                <td>Totale Interventi</td>
                <td>{{ number_format($m['summary']['intervention_total_price'], 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>Oneri accessori impresa forestale</td>
                <td>{{ number_format($m['summary']['company_price'], 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>Gestione e certificazioni</td>
                <td>{{ number_format($m['summary']['certification_and_management_price'], 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td style="background-color:yellow;"><strong>Totale Costo Mantenimento Certificato</strong></td>
                <td style="background-color:yellow;">
                    <strong>{{ number_format($catalogArea->catalog_estimate['maintenance']['summary']['total_net_price'], 2, ',', '.') }}
                        €</strong>
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px;">{{ $sisteco['vat']['label'] }}</td>
                <td style="font-size: 10px;">
                    {{ number_format($catalogArea->catalog_estimate['maintenance']['summary']['total_vat'], 2, ',', '.') }}
                    €</td>
            </tr>
            <tr>
                <td style="font-size: 10px;">Totale con IVA</td>
                <td style="font-size: 10px;">
                    {{ number_format($catalogArea->catalog_estimate['maintenance']['summary']['total_gross_price'], 2, ',', '.') }}
                    € </td>
            </tr>
        </table>
        <hr>
        <div class="pagebreak"> </div>
        <h2>Gestione forestale attiva e responsabile boschi del Monte Pisano</h2>

        <h2>Totale generale</h2>
        <table class="total-table column-nd-text-right">
            <tbody style="background-color: #B3C6E7;">
                <tr>
                    <th style="background-color: #B3C6E7;">Totale Generale interventi + mantenimento</th>
                    <td style="background-color: #B3C6E7;">
                        <strong>{{ number_format($g['total_net_price'], 2, ',', '.') }}</strong>
                        €
                    </td>
                </tr>
                <tr style="font-size:10px;">
                    <th style="background-color: #B3C6E7;">IVA</th>
                    <td style="background-color: #B3C6E7;">
                        {{ number_format($g['total_vat_price'], 2, ',', '.') }}
                </tr>
                <tr style="font-size:10px;">
                    <th style="background-color: #B3C6E7;">Totale Generale con IVA</th>
                    <td style="background-color: #B3C6E7;">
                        {{ number_format($g['total_gross_price'], 2, ',', '.') }}
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
                    <td style="background-color: #B3C6E7;">
                        {{ number_format($g['total_gross_price_per_area'], 2, ',', '.') }}
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        window.setTimeout(function() {
            $("#success-alert").fadeTo(500, 0).slideUp(500, function() {
                $(this).remove();
            });
            $("#error-alert").fadeTo(500, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 3000);
    </script>
</body>

</html>
