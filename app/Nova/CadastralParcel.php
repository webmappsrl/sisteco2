<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\BelongsToMany;
use Wm\MapMultiPolygon\MapMultiPolygon;
use Laravel\Nova\Http\Requests\NovaRequest;

class CadastralParcel extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CadastralParcel>
     */
    public static $model = \App\Models\CadastralParcel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'code';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'code', 'municipality'
    ];

    /**
     * Number of displayed items per page.
     * @var int
     * 
     */
    public static $perPageViaRelationship = 50;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Codice Catastale', 'code')
                ->sortable(),
            Text::make('Comune', 'municipality'),
            Currency::make('Stima', 'estimated_value')
                ->currency('EUR')
                ->sortable()
                ->displayUsing(function ($value) {
                    return number_format($value, 2, ',', '.') . ' €';
                }),
            Text::make('Area (mq)', 'square_meter_surface')
                ->sortable()
                ->displayUsing(function ($value) {
                    return number_format($value, 2, ',', '.') . ' MQ';
                }),
            Text::make('Pendenza media (º)', 'average_slope', function ($slope) {
                return str_replace('.', ',', round($slope, 2));
            })->onlyOnDetail(),
            Text::make('Distanza minima sentiero (m)', 'meter_min_distance_path', function ($distance) {
                return intval($distance) . ' m';
            })->onlyOnDetail(),
            Text::make('Distanza minima strada (m)', 'meter_min_distance_road', function ($distance) {
                return intval($distance) . ' m';
            })->onlyOnDetail(),
            Text::make('Classe Trasporto', 'way')->onlyOnDetail(),
            Text::make(
                'Interventi Forestali',
                function () {
                    if (is_null($this->catalog_estimate)) {
                        return 'ND';
                    }
                    $items = $this->catalog_estimate['interventions']['items'];
                    $info = $this->catalog_estimate['interventions']['info'];

                    $o = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                    $o .= '<table border="1">';
                    $o .= "<tr>";
                    $o .= "<th>Codice Intervento</th>";
                    $o .= "<th>Area</th>";
                    $o .= "<th>€/Ettaro</th>";
                    $o .= "<th>Totale (€)</th>";
                    $o .= "</tr>";

                    //create headings for interventions info table: Area(ettari) e Totale(€). Insert only intervention_area value in the column Area
                    $i = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                    $i .= '<table border="1">';
                    $i .= "<tr>";
                    $i .= "<th colspan='3'>Info</th>";
                    $i .= "<th colspan='1'>Area</th>";
                    $i .= "<th>€</th>";
                    $i .= "</tr>";

                    //if $items is empty display a row with ND
                    if (empty($items)) {
                        $o .= "<tr>";
                        $o .= "<td>ND</td>";
                        $o .= "<td>ND</td>";
                        $o .= "<td>ND</td>";
                        $o .= "<td>ND</td>";
                        $o .= "</tr>";
                    }

                    //if $info is empty display a row with ND
                    if (empty($info)) {
                        $i .= "<tr>";
                        $i .= "<td colspan='4'>ND</td>";
                        $i .= "</tr>";
                    }

                    foreach ($items as $item) {
                        $o .= "<tr>";
                        $o .= $item['code'] ? "<td>{$item['code']}</td>" : "<td>ND</td>";
                        $o .= $item['area'] ? "<td>{$item['area']}</td>" : "<td>ND</td>";
                        $o .= $item['unit_price'] ? "<td>{$item['unit_price']}</td>" : "<td>ND</td>";
                        $o .= $item['price'] ? "<td>{$item['price']}</td>" : "<td>ND</td>";
                        $o .= "</tr>";
                    }

                    foreach ($info as $key => $value) {
                        $i .= "<tr>";
                        $i .= "<td colspan='3'>{$key}</td>";
                        //only if $key == intervention_area insert the value in the column Area and not in the column €
                        if ($key == 'intervention_area') {
                            $i .= "<td>{$value}</td>";
                            $i .= "<td>ND</td>";
                        } else {
                            $i .= "<td>ND</td>";
                            $i .= "<td>{$value}</td>";
                        }
                        $i .= "</tr>";
                    }

                    $o .= "</table>";
                    $i .= "</table>";
                    return [$o, $i];
                }
            )->asHtml()->onlyOnDetail(),
            Text::make('Mantenimento', function () {
                if (is_null($this->catalog_estimate)) {
                    return 'ND';
                }

                $maintenanceItems = $this->catalog_estimate['maintenance']['items'];
                $maintenanceSummary = $this->catalog_estimate['maintenance']['summary'];
                $maintenanceCertifications = $this->catalog_estimate['maintenance']['certifications'];

                //create headings for maintenance items table
                $o = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                $o .= '<table border="1">';
                $o .= "<tr>";
                $o .= "<th>Codice Intervento</th>";
                $o .= "<th>Area</th>";
                $o .= "<th>€/Ettaro</th>";
                $o .= "<th>Totale(€)</th>";
                $o .= "</tr>";

                //create headings for maintenance summary table
                $i = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                $i .= '<table border="1">';
                $i .= "<tr>";
                $i .= "<th colspan='3'>Summary</th>";
                $i .= "<th colspan='3'>€</th>";
                $i .= "</tr>";

                //create headings for maintenance certifications table
                $u = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                $u .= '<table border="1">';
                $u .= "<tr>";
                $u .= "<th>Certifications</th>";
                $u .= "<th>€</th>";
                $u .= "</tr>";


                foreach ($maintenanceItems as $item) {
                    $o .= "<tr>";
                    $o .= $item['code'] ? "<td>{$item['code']}</td>" : "<td>ND</td>";
                    $o .= $item['area'] ? "<td>{$item['area']}</td>" : "<td>ND</td>";
                    $o .= $item['unit_price'] ? "<td>{$item['unit_price']}</td>" : "<td>ND</td>";
                    $o .= $item['price'] ? "<td>{$item['price']}</td>" : "<td>ND</td>";
                    $o .= "</tr>";
                }

                foreach ($maintenanceCertifications as $certification) {
                    $u .= "<tr>";
                    $u .= $certification['code'] ? "<td>{$certification['code']}</td>" : "<td>ND</td>";
                    $u .= $certification['price'] ? "<td>{$certification['price']}</td>" : "<td>ND</td>";
                    $u .= "</tr>";
                }

                foreach ($maintenanceSummary as $key => $value) {
                    $i .= "<tr>";
                    $i .= "<td colspan='3'>{$key}</td>";
                    $i .= "<td>{$value}</td>";
                    $i .= "</tr>";
                }
                $o .= "</table>";
                $i .= "</table>";
                $u .= "</table>";
                return [$o, $u, $i];
            })->asHtml()->onlyOnDetail(),
            Text::make('Costi Generali', function () {
                if (is_null($this->catalog_estimate)) {
                    return 'ND';
                }

                $generals = $this->catalog_estimate['general'];

                $o = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                $o .= '<table border="1">';
                $o .= "<tr>";
                $o .= "<th>Costi Generali</th>";
                $o .= "<th>€</th>";
                $o .= "</tr>";

                foreach ($generals as $key => $value) {
                    $o .= "<tr>";
                    $o .= "<td>{$key}</td>";
                    $o .= "<td>{$value}</td>";
                    $o .= "</tr>";
                }

                $o .= "</table>";
                return $o;
            })->asHtml()->onlyOnDetail(),
            BelongsToMany::make('Proprietari', 'owners', Owner::class),
            Text::make('Link')
                ->resolveUsing(function ($value, $resource, $attribute) {
                    return '<a class="link-default" target="_blank" href="' . route(
                        'cadastral-parcel',
                        ['id' => $resource->id]
                    ) . '">View Parcel</a>';
                })
                ->asHtml()
                ->exceptOnForms(),
            MapMultiPolygon::make('Geometry', 'geometry')->withMeta([
                'center' => ['42.795977075', '10.326813853'],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
            ])->hideFromIndex()

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
