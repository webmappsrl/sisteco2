<?php

namespace App\Console\Commands;

use App\Models\CatalogArea;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportInterventionYearFromGeojsonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-intervention-year-from-geojson-command {path : The path to the geojson file on the server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import intervention year from a geojson file based on the position of the intervention';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path');
        if (!File::exists($path)) {
            $this->error("The file {$path} does not exist.");
            return;
        }

        $content = File::get($path);
        $geojson = json_decode($content, true);

        if ($geojson === null) {
            $this->error("The file {$path} does not contain valid GeoJSON.");
            return;
        }

        // Qui puoi fare qualcosa con i dati GeoJSON
        // Ad esempio, stampiamo il tipo di GeoJSON
        if (isset($geojson['type'])) {
            $this->info("The GeoJSON type is: " . $geojson['type']);
        }

        // Esempio per iterare le features se presenti
        if (isset($geojson['features']) && is_array($geojson['features'])) {
            foreach ($geojson['features'] as $count => $feature) {
                $this->info("Processing feature " . $count . " of " . count($geojson['features']));
                if ($count == 77) {
                    // NOTE:
                    // The 77th feature is a not a conform MultiPolygon, which is not supported by ST_GeomFromGeoJSON
                    continue;
                }
                // $catalogArea = DB::select("SELECT * FROM catalog_areas WHERE ST_Intersects(geometry(geometry), ST_GeomFromGeoJSON('".json_encode($feature['geometry'])."')::geometry)");
                $geoJson = json_encode($feature['geometry']);
                $catalogArea = DB::select("
                    SELECT
                    a.id AS catalog_area_id,
                    ST_Area(ST_Intersection(a.geometry, ST_GeomFromGeoJSON(?))) AS intersection_area
                    FROM
                    catalog_areas a
                    WHERE
                    ST_Intersects(a.geometry, ST_GeomFromGeoJSON(?)::geometry)
                    ORDER BY
                    intersection_area DESC
                ", [$geoJson, $geoJson]);

                if ($catalogArea && count($catalogArea) > 0) {
                    // Associate the intervention year with the CatalogArea
                    $catalogArea = CatalogArea::find($catalogArea[0]->catalog_area_id);
                    $catalogArea->work_start_date = array_key_exists('anno_int',$feature['properties']) ? $feature['properties']['anno_int'] : null;
                    $this->info("The catalog area ". $catalogArea->id. " year is: " . $feature['properties']['anno_int']);
                    $catalogArea->save();
                }
            }
        }

    }
}
