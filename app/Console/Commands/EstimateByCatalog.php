<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Catalog;
use App\Models\CadastralParcel;
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
    protected $signature = 'sisteco2:estimate_by_catalog 
                            {id : id of the catalog that must be used to estimate}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command loop on all cadastral particles and compute the estimated value on a specific catalog identified by id';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle()
    {
        $this->info('Processing');
        $ids = collect(DB::select('select distinct cadastral_parcel_id as id from cadastral_parcel_owner;'))->pluck('id')->toArray();

        $parcels = CadastralParcel::whereIn('id', $ids)->get();
        $tot_p = $parcels->count();
        $count_p = 1;

        // Loop on particles
        foreach ($parcels as $p) {
            $p->catalog_estimate = $p->computeCatalogEstimate($this->argument('id'));
            $p->estimated_value = $p->catalog_estimate->general->total_gross_price;
            // $p->estimated_value = $p->catalog_estimate['general']['total_gross_price'];
            $p->save();
            $count_p++;

            //print the json
            // $this->info(json_encode($json));
            $this->info(
                "Processing {$count_p} of {$tot_p} particles"
            );
        }
        return $this->info('Done!');
    }
}