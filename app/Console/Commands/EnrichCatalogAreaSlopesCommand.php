<?php

namespace App\Console\Commands;

use App\Models\CatalogArea;
use Illuminate\Console\Command;

class EnrichCatalogAreaSlopesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco:area-slopes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command compute alla slope stats info for catalog areas and save them in proper fileds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $areas = CatalogArea::get();
        $areaBar = $this->output->createProgressBar($areas->count());
        $areaBar->start();
        foreach ($areas as $area) {
            try {
                $stats = $area->computeSlopeStats();
                $area->slope_min = $stats['slope_min'];
                $area->slope_max = $stats['slope_max'];
                $area->slope_avg = $stats['slope_avg'];
                $area->save();
            } catch (\Throwable $th) {
                $this->error("An error occured while calculating stats info for catalogArea {$area->id}");
            }
            $areaBar->advance();
        }
    }
}
