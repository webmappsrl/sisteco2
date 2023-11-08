<?php

namespace App\Console\Commands;

use App\Models\CatalogArea;
use Illuminate\Console\Command;

class EnrichCatalogAreaWithStreetsInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco:enrich-streets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command adds street info to catalog areas (streets_min_dist)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $areas = CatalogArea::all();
        $this->info("sisteco:enrich-streets");
        $bar = $this->output->createProgressBar($areas->count());

        foreach($areas as $area) {
            $area->streets_min_dist=$area->getStreetsMinDist();
            $area->save();
            $bar->advance();
        }
    }
}
