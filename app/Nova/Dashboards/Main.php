<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\TotalOwners;
use App\Nova\Metrics\TotalEstimatedValue;
use App\Nova\Metrics\TotalCadastralParcels;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new TotalOwners,
            new TotalCadastralParcels,
            new TotalEstimatedValue,
        ];
    }
}