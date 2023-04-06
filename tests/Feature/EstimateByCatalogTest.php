<?php

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use App\Models\Catalog;
use App\Models\CatalogArea;
use App\Models\CatalogType;
use App\Models\CadastralParcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Exception\RuntimeException;

class EstimateByCatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test's variables
     * @var \App\Models\Catalog
     * @var \App\Models\CadastralParcel
     * @var $parcelGeom string
     * @return void
     */
    private $catalog;
    private $parcel;
    private $parcelGeom;
    private $catalogEstimateParcel;
    private $catalogAreas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catalog = Catalog::factory()->create([
            'name' => 'MIPAAF 2023 / Rilievi',
            'description' => 'test',
            'sisteco_legacy_id' => 1
        ]);

        //take the CadastralParcelGeometry.json from Stubs folder
        $path = __DIR__ . '/Stubs/CadastralParcelGeometry.json';
        $geojson = json_decode(file_get_contents($path), true);
        $geojson_content = json_encode($geojson['geometry']);
        $sql = "SELECT ST_AsText(ST_Force2D(ST_CollectionExtract(ST_Polygonize(ST_GeomFromGeoJSON('" . $geojson_content . "')), 3))) As wkt";
        $this->parcelGeom = DB::select($sql)[0]->wkt;

        //take the CatalogEstimateParcel.json from Stubs folder
        $path = __DIR__ . '/Stubs/CatalogEstimateCadastralParcel.json';
        $this->catalogEstimateParcel = json_decode(file_get_contents($path), true);

        //create a cadastral parcel
        $this->parcel = CadastralParcel::factory()->create([
            'code' => 'B390_000700.176',
            'municipality' => "Calci",
            'estimated_value' => "124115.20692265",
            'average_slope' => "29.257606286215",
            'meter_min_distance_road' => 0,
            'meter_min_distance_path' => 72,
            'square_meter_surface' => 0,
            'slope' => 2,
            'way' => 1,
            'geometry' => $this->parcelGeom,
            'catalog_estimate' => $this->catalogEstimateParcel

        ]);

        //TODO create 12 catalog areas taking the ones with ids(23,68,69,71,124,283,284,285,286,287,288,311) from the database

    }

    /**
     * Test computeCatalogEstimate method return array with correct keys.
     * 
     * @return void
     */
    public function test_compute_catalog_estimate_method_return_array_with_correct_main_keys()
    {
        $result = $this->parcel->computeCatalogEstimate($this->catalog->id);

        // Check if the result has the expected keys
        $this->assertArrayHasKey('interventions', $result);
        $this->assertArrayHasKey('maintenance', $result);
        $this->assertArrayHasKey('general', $result);

        //     // Check if the 'interventions' array has the expected keys
        //     $interventions = $result['interventions'];
        //     $this->assertArrayHasKey('items', $interventions);
        //     $this->assertArrayHasKey('info', $interventions);

        //     // Check if the 'items' array has the expected structure
        //     $items = $interventions['items'];
        //     $this->assertIsArray($items);
        //     if (count($items) > 0) {
        //         foreach ($items as $item) {
        //             $this->assertArrayHasKey('code', $item);
        //             $this->assertArrayHasKey('area', $item);
        //             $this->assertArrayHasKey('unit_price', $item);
        //             $this->assertArrayHasKey('price', $item);
        //         }
        //     }

        //     // Check if the 'info' array has the expected keys
        //     $info = $interventions['info'];
        //     $this->assertArrayHasKey('intervention_area', $info);
        //     $this->assertArrayHasKey('intervention_price', $info);
        //     $this->assertArrayHasKey('supervision_price', $info);
        //     $this->assertArrayHasKey('overhead_price', $info);
        //     $this->assertArrayHasKey('business_profit_price', $info);
        //     $this->assertArrayHasKey('intervention_certification', $info);
        //     $this->assertArrayHasKey('total_intervention_certificated_price', $info);
        //     $this->assertArrayHasKey('team_price', $info);
        //     $this->assertArrayHasKey('platform_maintenance_price', $info);
        //     $this->assertArrayHasKey('total_intervention_gross_price', $info);
        //     $this->assertArrayHasKey('total_intervention_net_price', $info);
        //     $this->assertArrayHasKey('total_intervention_vat', $info);
        //     $this->assertArrayHasKey('intervention_gross_price_per_area', $info);
    }

    /**
     * Test if the command exists
     * 
     * @return void
     */
    public function test_estimate_by_catalog_command_exists()
    {
        $this->assertTrue(class_exists(\App\Console\Commands\EstimateByCatalog::class));
    }

    /**
     * Test the estimate by catalog command without id.
     * 
     * @return void
     */
    public function test_estimate_by_catalog_without_id()
    {
        $this->expectException(RuntimeException::class);
        $this->artisan('sisteco2:estimate_by_catalog')
            ->assertExitCode(1);
    }

    /**
     * Test the estimate by catalog command with id.
     * 
     * @return void
     */
    public function test_estimate_by_catalog_with_id()
    {
        //create a catalog

        $this->artisan('sisteco2:estimate_by_catalog', ['id' => $this->catalog->id])
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }


    /**
     * Test computeCatalogEstimate method throws exception for invalid catalog id.
     *
     * @return void
     */
    public function test_compute_catalog_estimate_method_throws_exception_for_invalid_catalog_id()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Catalog with id 9999 not found');

        $this->catalog->id = 9999;
        $this->parcel->computeCatalogEstimate($this->catalog->id);
    }

    /**
     * Test computeCatalogEstimate method return array if valid catalog id.
     * 
     * @return void
     */
    public function test_compute_catalog_estimate_method_return_array_for_valid_catalog_id()
    {
        $result = $this->parcel->computeCatalogEstimate($this->catalog->id);

        $this->assertIsArray($result);
    }
}
