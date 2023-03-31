<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanEstimatedValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco2:clean_estimate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean estimated value for all cadastral parcels and empty the catalog_estimate table.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Cleaning estimated value for all cadastral parcels..');
        DB::table('cadastral_parcels')->update(['estimated_value' => 0]);

        $this->info('Emptying catalog_estimate table..');
        DB::table('cadastral_parcels')->update(['catalog_estimate' => null]);

        $this->info('Done!');
    }
}
