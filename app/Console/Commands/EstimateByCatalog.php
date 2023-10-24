<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Catalog;
use App\Models\CadastralParcel;
use App\Models\CatalogArea;
use Faker\Core\Number;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EstimateByCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco2:estimate_by_catalog {id?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command loop on all catalog areas and compute the estimated value. If an id is passed, it will compute only for that catalog area.';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle()
    {

        if ($this->argument('id')) {
            $this->info('Processing only catalog area with id: ' . $this->argument('id'));
            $catalog_area = CatalogArea::find($this->argument('id'));
            if (!$catalog_area) {
                throw new Exception('Catalog area not found');
            }
            $catalog_area->catalog_estimate =  $catalog_area->computeCatalogEstimate();
            $estimate = $catalog_area->catalog_estimate['general']['total_gross_price'] ?? 0;
            $estimate = str_replace(".", "", $estimate); // Remove the dots
            $estimate = str_replace(",", ".", $estimate); // Replace the comma with a dot
            //update the estimated value
            $catalog_area->estimated_value = $estimate;
            $catalog_area->save();
        } else {
            $catalog_areas = CatalogArea::all();
            $tot = $catalog_areas->count();
            $count = 1;
            $catalogBar = $this->output->createProgressBar($tot);
            $catalogBar->start();
            foreach ($catalog_areas as $catalog_area) {
                $catalog_area->catalog_estimate =  $catalog_area->computeCatalogEstimate();
                $estimate = $catalog_area->catalog_estimate['general']['total_gross_price'] ?? 0;
                $estimate = str_replace(".", "", $estimate); // Remove the dots
                $estimate = str_replace(",", ".", $estimate); // Replace the comma with a dot
                //update the estimated value
                $catalog_area->estimated_value = $estimate;
                $catalog_area->save();
                $count++;
                $catalogBar->advance();
            }
            $catalogBar->finish();
        }
        return $this->info(PHP_EOL . 'Done!');
    }
}
