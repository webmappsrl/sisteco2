<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\TotalOwners;
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
            new TotalOwners
        ];
    }
}
