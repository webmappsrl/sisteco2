<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class CatalogType extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CatalogType>
     */
    public static $model = \App\Models\CatalogType::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'cod_int'
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
            Text::make('cod_int'),
            Text::make('name'),
            KeyValue::make('prices')
                ->rules('json')
                ->keyLabel('Type')
                ->valueLabel('Price (€)')
                ->hideFromIndex(),
            Text::make('Areas #', function () {
                return $this->catalogAreas()->count();
            }),
            BelongsTo::make('Catalog')->readonly(),
            Text::make('Color')->hideFromIndex(),
            Text::make('Maintenance Price Fist Year', 'maintenance_price_fist_year')->displayUsing(function ($value) {
                return '<p style="color:green; text-align:left;">' . number_format($value, 2, ',', '.') . '€' .  '</p>';
            })->sortable()->asHtml()->hideFromIndex(),
            Text::make('Maintenance Price Second Year', 'maintenance_price_second_year')->displayUsing(function ($value) {
                return '<p style="color:green; text-align:left;">' . number_format($value, 2, ',', '.') . '€' .  '</p>';
            })->sortable()->asHtml()->hideFromIndex(),
            Text::make('Maintenance Price Third Year', 'maintenance_price_third_year')->displayUsing(function ($value) {
                return '<p style="color:green; text-align:left;">' . number_format($value, 2, ',', '.') . '€' .  '</p>';
            })->sortable()->asHtml()->hideFromIndex(),
            Text::make('Maintenance Price Fourth Year', 'maintenance_price_fourth_year')->displayUsing(function ($value) {
                return '<p style="color:green; text-align:left;">' . number_format($value, 2, ',', '.') . '€' .  '</p>';
            })->sortable()->asHtml()->hideFromIndex(),
            Text::make('Maintenance Price Fifth Year', 'maintenance_price_fifth_year')->displayUsing(function ($value) {
                return '<p style="color:green; text-align:left;">' . number_format($value, 2, ',', '.') . '€' .  '</p>';
            })->sortable()->asHtml()->hideFromIndex(),


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