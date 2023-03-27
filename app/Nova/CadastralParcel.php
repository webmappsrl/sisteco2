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
            Text::make('Area (mq)', 'area')
                ->sortable()
                ->displayUsing(function ($value) {
                    return number_format($value, 2, ',', '.') . ' MQ';
                }),
            Text::make('Pendenza media (º)', 'average_slope', function (string $slope) {
                return str_replace('.', ',', round($slope, 2));
            })->onlyOnDetail(),
            Text::make('Distanza minima sentiero (m)', 'meter_min_distance_path', function (string $distance) {
                return intval($distance) . ' m';
            })->onlyOnDetail(),
            Text::make('Distanza minima strada (m)', 'meter_min_distance_road', function (string $distance) {
                return intval($distance) . ' m';
            })->onlyOnDetail(),
            Text::make('Classe Trasporto', 'way')->onlyOnDetail(),
            Text::make(
                'Dettaglio Stima',
                function () {
                    if (is_null($this->catalog_estimate)) {
                        return 'ND';
                    }
                    if (!isset($this->catalog_estimate['items'])) {
                        return 'ND';
                    }
                    if (count($this->catalog_estimate['items']) == 0) {
                        return 'ND';
                    }
                    $o = '<style> table, th, td { border: 1px solid black; padding: 5px;}</style>';
                    $o .= '<table border="1">';
                    $o .= "<tr>";
                    $o .= "<th>COD_INT</th>";
                    $o .= "<th>Area</th>";
                    $o .= "<th>Unit Price</th>";
                    $o .= "<th>Price</th>";
                    $o .= "</tr>";
                    foreach ($this->catalog_estimate['items'] as $item) {
                        $o .= "<tr>";
                        $o .= "<td>{$item['code']}</td>";
                        $o .= "<td>{$item['area']}</td>";
                        $o .= "<td>{$item['unit_price']}</td>";
                        $o .= "<td>{$item['price']}</td>";
                        $o .= "</tr>";
                    }

                    $o .= "</table>";
                    return $o;
                }
            )->asHtml()->onlyOnDetail(),
            BelongsToMany::make('Proprietari', 'owners', Owner::class),
            MapMultiPolygon::make('Geometry', 'geometry')->withMeta([
                'center' => ['42.795977075', '10.326813853'],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
            ]),

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
