<?php

namespace App\Console\Commands;

use App\Models\Catalog;
use App\Models\CatalogArea;
use App\Models\CatalogType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco:import_catalog
    {path : Absolute path to geojson to be imported}
    {catalog_id : Id of the Catalog used to assign all the areas}
    {field : Name of the properties field to be used to find CatalogType}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a geojson file to a Catalog (Catalog and types must be already defined)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = $this->argument('path');
        if(!file_exists($file)) {
            throw new Exception("Geojson file $file not found.");
        }

        // READ FILE
        $json = json_decode(file_get_contents($file),TRUE);
        $areas = $json['features'];
        $tot = count($areas);
        $this->info("Found $tot areas");

        // Find poper catalog
        $c = Catalog::find($this->argument('catalog_id'));
        if(empty($c)) {
            throw new Exception("Catalog with id {$this->argument('catalog_id')} does not exixt. First create it.", 1);
            
        }

        // Loop on areas
        $count = 1;
        $types = $c->catalogTypes()->pluck('id','cod_int')->toArray();
        foreach ($areas as $area) {
            $counter = "$count / $tot";
            $count ++;
            // Find type
            if(array_key_exists($area['properties'][$this->argument('field')],$types)) {
                $this->info("$counter - Processing AREA");
                // Import AREA into geometry field
                $geometry = json_encode($area['geometry']);
                CatalogArea::create([
                    'catalog_id' => $this->argument('catalog_id'),
                    'catalog_type_id' => $types[$area['properties'][$this->argument('field')]],
                    'geometry' => DB::raw("ST_GeomFromGeoJSON('$geometry')"),
                ]);
            } else {
                $this->warn("$counter - TYPE {$area['properties'][$this->argument('field')]} NOT FOUND: skipping Area");
            }
        }

        return Command::SUCCESS;
    }
}
