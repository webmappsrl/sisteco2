<?php

namespace App\Nova;

use App\Models\CatalogArea;
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

            BelongsToMany::make('Proprietari', 'owners', Owner::class),
            Text::make(
                'Catalog Areas',
                function () {
                    $areas = $this->getCatalogAreasByGeometry(1);
                    $areasString = '';
                    if ($areas) {
                        foreach ($areas as $area) {
                            $catalogArea = CatalogArea::find($area);
                            $areasString .= '<a style="color:lightblue;" href="/resources/catalog-areas/' . $catalogArea->id . '">' . $catalogArea->id . '</a>, ';
                        }
                        return substr($areasString, 0, -2);
                    }
                }
            )->asHtml()->onlyOnDetail(),
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
