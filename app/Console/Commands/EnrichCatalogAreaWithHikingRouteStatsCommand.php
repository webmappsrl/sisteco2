<?php

namespace App\Console\Commands;

use App\Models\CatalogArea;
use App\Models\HikingRoute;
use Illuminate\Console\Command;

class EnrichCatalogAreaWithHikingRouteStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco:hiking-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Commands add hiking routes information to Catalog Area';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $areas = CatalogArea::all();
        $bar = $this->output->createProgressBar($areas->count());
        foreach($areas as $area) {
            $hr_stats = $area->computeHikingRoutes();
            if(count($hr_stats)>0) {
                $area->hiking_routes_length=array_sum($hr_stats);
                $area->hiking_routes_details=$hr_stats;
            }
            else {
                $area->hiking_routes_min_dist=$area->getHikingRouteMinDist();
            }
            $area->save();
            $bar->advance();
        }
    }
}
