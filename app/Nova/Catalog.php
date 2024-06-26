<?php

namespace App\Nova;

use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Http\Requests\NovaRequest;

class Catalog extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Catalog>
     */
    public static $model = \App\Models\Catalog::class;

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
        'id', 'name',
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
            Text::make('Name'),
            Text::make('Map Url', 'map_url')
                ->hideFromDetail()
                ->hideFromIndex()
                ->rules('nullable', 'url:http,https')
                ->help('Must be a valid URL'),
            Text::make('Map Url', function () {
                $url = $this->map_url;
                $string = '<a target="_blank" style="color: #2697bc" href="' . $url . '">' . $url . '</a>';
                return $string;
            })->asHtml()->showOnDetail()->showOnUpdating()->showOnCreating(),
            Text::make('Designer Emails')
                ->hideFromIndex()
                ->help("Comma separated emails (eg. email@example.com,test@example.com)")
                ->rules('nullable', function ($attribute, $value, $fail) {
                    $emails = array_map('trim', explode(',', $value));

                    foreach ($emails as $email) {
                        if (strlen($email) < 1) {
                            $fail('No Email address provided after the comma. Please modify accordingly.');
                            return;
                        }
                        $validator = Validator::make(['email' => $email], ['email' => 'email']);

                        if ($validator->fails()) {
                            $fail('The ' . $attribute . ' must contain valid email addresses separated by comma.');
                            return;
                        }
                    }
                }),
            Number::make('Areas', function () {
                return $this->catalogAreas()->count();
            })->onlyOnIndex(),
            HasMany::make('Catalog Types', 'catalogTypes', CatalogType::class),
            HasMany::make('Catalog Areas', 'catalogAreas', CatalogArea::class),

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
