<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Owner extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Owner>
     */
    public static $model = \App\Models\Owner::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'last_name' ?? 'first_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'country'
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
            Text::make('Nome', 'first_name')
                ->sortable()
                ->required(),
            Text::make('Cognome', 'last_name')
                ->sortable()
                ->required(),
            Text::make('Email', 'email')
                ->rules(
                    'required',
                    'email',
                    'max:255'
                ),
            Text::make('Telefono', 'phone')
                ->rules(
                    'required',
                    'regex:/^[0-9]{10}$/', //* Regex for phone number with 10 digits
                ),
            Text::make('Codice Fiscale', 'fiscal_code')
                ->rules(
                    'required',
                ),
            Text::make('Indirizzo', function () {
                return $this->{'addr:street'} . ' ' . $this->{'addr:housenumber'} . ', ' . $this->{'addr:city'} . ' ' . $this->{'addr:province'} . ' (' . $this->{'addr:postcode'} . ')' . ' ' . $this->{'addr:locality'};
            })
                ->onlyOnDetail(),
            Text::make('Partita IVA', 'vat_number')
                ->hideFromIndex()
                ->rules(
                    'required',
                ),
            Text::make('Nome Azienda', 'business_name')
                ->hideFromIndex(),
            Text::make('Via', 'addr:street')
                ->onlyOnForms(),
            Text::make('Civico', 'addr:housenumber')
                ->onlyOnForms(),
            Text::make('Città', 'addr:city')
                ->onlyOnForms(),
            Text::make('CAP', 'addr:postcode')
                ->onlyOnForms(),
            Text::make('Provincia', 'addr:province')
                ->onlyOnForms(),
            Text::make('Localitá', 'addr:locality')
                ->onlyOnForms(),
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
