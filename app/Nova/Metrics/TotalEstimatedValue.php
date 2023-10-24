<?php

namespace App\Nova\Metrics;

use App\Models\CadastralParcel;
use App\Models\CatalogArea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class TotalEstimatedValue extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $total = 0;
        $catalogAreas = CatalogArea::all();

        foreach ($catalogAreas as $area) {
            $total += $area->estimated_value;
        }

        return $this->result($total)->format('0.00')->prefix('€ ');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => __('30 Days'),
            60 => __('60 Days'),
            365 => __('365 Days'),
            'TODAY' => __('Today'),
            'MTD' => __('Month To Date'),
            'QTD' => __('Quarter To Date'),
            'YTD' => __('Year To Date'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the name of the metric.
     * 
     * @return string
     */

    public function name()
    {
        return __('Total Estimated Catalog Areas Value');
    }
}
