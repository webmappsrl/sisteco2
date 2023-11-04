<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;

class SyncCaiHikingRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco:sync-cai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Hiking Routes from CAI database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get List
        $list = HikingRoute::getListFromCai();
        $bar = $this->output->createProgressBar(count($list));

        foreach ($list as $cai_id => $updated_at) {
            HikingRoute::importFromCai($cai_id);
            $bar->advance();
        }
    }
}
