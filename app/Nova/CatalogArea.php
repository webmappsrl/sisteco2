<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\BelongsTo;
use Wm\MapMultiPolygon\MapMultiPolygon;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\Date;

class CatalogArea extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CatalogArea>
     */
    public static $model = \App\Models\CatalogArea::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'owners'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Catalog')->readonly(),
            BelongsTo::make('Catalog Type')->readonly(),
            $this->estimated_value > 0 ? Text::make('Valore Stimato', 'estimated_value')->displayUsing(function ($value) {
                return '<p style="color:green; text-align:left;">' . number_format($value, 2, ',', '.') . '€' .  '</p>';
            })->sortable()->asHtml()
                :
                Text::make('Valore Stimato', 'estimated_value', function ($value) {
                    return '<p style="color:red">Nessuna stima disponibile</p>';
                })->sortable()->asHtml(),
            Text::make('Link')
                ->resolveUsing(function ($value, $resource, $attribute) {
                    return '<a class="link-default" target="_blank" href="' . route(
                        'catalog-area',
                        ['id' => $resource->id]
                    ) . '">View Area</a>';
                })
                ->asHtml()
                ->exceptOnForms(),
            Text::make('Data Inizio Lavori', 'work_start_date')->sortable(),
            MapMultiPolygon::make('Geometry')->withMeta([
                'center' => ['42.795977075', '10.326813853'],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
            ])->hideFromIndex(),
            Text::make('Area', function () {
                $area
                    = DB::table('catalog_areas')
                    ->select(DB::raw('ST_Area(geometry) as area'))
                    ->where('id', $this->id)
                    ->value('area');
                return number_format($area, 2, ',', '.') . ' MQ';
            })->hideFromIndex()
                ->sortable(),
            Text::make(
                'Interventi Forestali',
                function () {
                    //if $this->catalog_estimate is an empty array display ND
                    if (empty($this->catalog_estimate)) {
                        return '<p style="color:red">ND</p>';
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
                if (empty($this->catalog_estimate)) {
                    return '<p style="color:red">ND</p>';
                }
                $o = $u = $i = '';

                $maintenanceItems = (isset($this->catalog_estimate['maintenance']['items']) && !empty($this->catalog_estimate['maintenance']['items'])) ? $this->catalog_estimate['maintenance']['items'] : '';
                $maintenanceSummary = (isset($this->catalog_estimate['maintenance']['summary']) && !empty($this->catalog_estimate['maintenance']['summary'])) ? $this->catalog_estimate['maintenance']['summary'] : '';
                $maintenanceCertifications = (isset($this->catalog_estimate['maintenance']['certifications']) && !empty($this->catalog_estimate['maintenance']['certifications'])) ? $this->catalog_estimate['maintenance']['certifications'] : '';

                if ($maintenanceItems) {
                    //create headings for maintenance items table
                    $o = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                    $o .= '<table border="1">';
                    $o .= "<tr>";
                    $o .= "<th>Codice Intervento</th>";
                    $o .= "<th>Area</th>";
                    $o .= "<th>€/Ettaro</th>";
                    $o .= "<th>Totale(€)</th>";
                    $o .= "</tr>";
                }

                if ($maintenanceCertifications) {
                    //create headings for maintenance certifications table
                    $u = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                    $u .= '<table border="1">';
                    $u .= "<tr>";
                    $u .= "<th>Certifications</th>";
                    $u .= "<th>€</th>";
                    $u .= "</tr>";
                }
                if ($maintenanceSummary) {
                    //create headings for maintenance summary table
                    $i = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                    $i .= '<table border="1">';
                    $i .= "<tr>";
                    $i .= "<th colspan='3'>Summary</th>";
                    $i .= "<th colspan='3'>€</th>";
                    $i .= "</tr>";
                }

                if ($maintenanceItems) {
                    foreach ($maintenanceItems as $item) {
                        $o .= "<tr>";
                        $o .= $item['code'] ? "<td>{$item['code']}</td>" : "<td>ND</td>";
                        $o .= $item['area'] ? "<td>{$item['area']}</td>" : "<td>ND</td>";
                        $o .= $item['unit_price'] ? "<td>{$item['unit_price']}</td>" : "<td>ND</td>";
                        $o .= $item['price'] ? "<td>{$item['price']}</td>" : "<td>ND</td>";
                        $o .= "</tr>";
                    }
                }

                if ($maintenanceCertifications) {
                    foreach ($maintenanceCertifications as $certification) {
                        $u .= "<tr>";
                        $u .= $certification['code'] ? "<td>{$certification['code']}</td>" : "<td>ND</td>";
                        $u .= $certification['price'] ? "<td>{$certification['price']}</td>" : "<td>ND</td>";
                        $u .= "</tr>";
                    }
                }

                if ($maintenanceSummary) {
                    foreach ($maintenanceSummary as $key => $value) {
                        $i .= "<tr>";
                        $i .= "<td colspan='3'>{$key}</td>";
                        $i .= "<td>{$value}</td>";
                        $i .= "</tr>";
                    }
                }

                if ($maintenanceItems) {
                    $o .= "</table>";
                }
                if ($maintenanceCertifications) {
                    $u .= "</table>";
                }
                if ($maintenanceSummary) {
                    $i .= "</table>";
                }
                return [$o, $u, $i];
            })->asHtml()->onlyOnDetail(),
            Text::make('Costi Generali', function () {
                if (empty($this->catalog_estimate)) {
                    return '<p style="color:red">ND</p>';
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
            Images::make('Featured Image', 'featured-image')
                ->hideFromIndex()
                ->croppable(false)
                ->customPropertiesFields([Text::make('Caption')]),
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
