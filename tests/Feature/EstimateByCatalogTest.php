<?php

namespace Tests\Feature;

/**
 * 
 * Esempio con dati
 * 
 *{
    "interventions" : {
        "items":[
            {
                "code" : "1.B.1",
                "area" : "7,4925",
                "unit_price" : "13.422,00",
                "price" : "122.688,49"
            },
            {
                "code" : "2.B.1",
                "area" : "3,3448",
                "unit_price" : "7.041,00",
                "price":"28.731,90"
            },
            {
                "code" : "3.B.1",
                "area" : "0,00",
                "unit_price" : "5.464,00",
                "price":"0,00"
            }
        ],
        "intervention_area" : "10,8373",
        "intervention_price" : "151.420,39",
        "supervision_price" : "15.142,04",
        "overhead_price" : "24.227,26",
        "business_profit_price" : "15.142,04",
        "intervention_certification" : "1.100,00",
        "total_intervention_certificated_price" : "207.031,73",
        "team_price" : "4.140,63",
        "platform_maintenance_price" : "4.140,63",
        "total_intervention_gross_price" : "215.313,00",
        "total_intervention_net_price" : "176.486,06",
        "total_intervention_vat" : "38.826,93",
        "intervention_gross_price_per_area" : "19.867,77"
        
    },
    "maintenance" : {
        "items":[
            {
                "code" : "year_1",
                "area" : "10,8373",
                "unit_price" : "1.000,0",
                "price" : "13.221,51"
            },
            {
                "code" : "year_2",
                "area" : "10,8373",
                "unit_price" : "1.000,0",
                "price" : "13.221,51"
            },
            {
                "code" : "year_3",
                "area" : "10,8373",
                "unit_price" : "1.000,0",
                "price" : "13.221,51"
            },
            {
                "code" : "year_4",
                "area" : "10,8373",
                "unit_price" : "1.000,00",
                "price" : "13.221,51"
            },            
            {
                "code" : "year_5",
                "area" : "10,8373",
                "unit_price" : "1.000,00",
                "price" : "13.221,51"
            },
            {
                "code" : "certification_year_2",
                "price" : "850,00"
            }
        ],
        "total_maintenance_gross_price" : "67.807,53",
        "total_maintenance_net_price" : "55.579,94",
        "total_maintenance_vat" : "12.227,59",
        "maintenance_gross_price_per_area" : "1.251,37"
    },
    "general" : {
        "total_gross_price" : "283.120,53",
        "total_net_price" : "232.066,01",
        "total_vat" : "51.054,52",
        "gross_price_per_area" : "4.354,11"
    }
}


 * 
 */


use Tests\TestCase;
use App\Models\Catalog;
use App\Models\CatalogArea;
use App\Models\CatalogType;
use App\Models\CadastralParcel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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
    private $catalogTypes;


    //create the data for the test
    public function createData(): void
    {

        //create the catalog 
        $this->catalog = Catalog::factory()->create([
            'name' => 'MIPAAF 2023 / Rilievi',
            'description' => 'test',
            'sisteco_legacy_id' => 1
        ]);
        $this->catalog->save();

        //create 4 catalog types getting data from the database catalog_types table
        $cType1 = CatalogType::factory()->create([
            'name' => 'Nessuna Lavorazione',
            'cod_int' => 0,
            'catalog_id' => $this->catalog->id,
            'sisteco_legacy_id' => 1,
            'prices' => ["A.1" => 0, "A.2" => 0, "A.3" => 0, "B.1" => 0, "B.2" => 0, "B.3" => 0, "C.1" => 0, "C.2" => 0, "C.3" => 0]
        ]);
        $cType1->save();
        $cType2 = CatalogType::factory()->create([
            'name' => 'Diradamento',
            'cod_int' => 1,
            'catalog_id' => $this->catalog->id,
            'sisteco_legacy_id' => 2,
            'prices' => ["A.1" => 12814, "A.2" => 12981, "A.3" => 13149, "B.1" => 13422, "B.2" => 13589, "B.3" => 13756, "C.1" => 13178, "C.2" => 13346, "C.3" => 13513]
        ]);
        $cType2->save();
        $cType3 = CatalogType::factory()->create([
            'name' => 'Taglio ceduo',
            'cod_int' => 2,
            'catalog_id' => $this->catalog->id,
            'sisteco_legacy_id' => 3,
            'prices' => ["A.1" => 7069, "A.2" => 8132, "A.3" => 9348, "B.1" => 7041, "B.2" => 8025, "B.3" => 9151, "C.1" => 7426, "C.2" => 10284, "C.3" => 11649]

        ]);
        $cType3->save();
        $cType4 = CatalogType::factory()->create([
            'name' => 'Avviamento',
            'cod_int' => 3,
            'catalog_id' => $this->catalog->id,
            'sisteco_legacy_id' => 4,
            'prices' => ["A.1" => 5411, "A.2" => 5959, "A.3" => 6726, "B.1" => 5464, "B.2" => 5971, "B.3" => 6681, "C.1" => 6230, "C.2" => 7386, "C.3" => 8121]
        ]);
        $cType4->save();

        $this->catalogTypes = [$cType1, $cType2, $cType3, $cType4];

        //take the CadastralParcelGeometry.json from Stubs folder
        $parcelPath = __DIR__ . '/Stubs/CadastralParcelGeometry.json';
        $parcelGeojson = json_decode(file_get_contents($parcelPath), true);
        $parcelGeojson_content = json_encode($parcelGeojson['geometry']);
        $sql = "SELECT ST_AsText(ST_Force2D(ST_CollectionExtract(ST_Polygonize(ST_GeomFromGeoJSON('" . $parcelGeojson_content . "')), 3))) As wkt";
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
        $this->parcel->save();

        //create 12 catalog areas taking the ones with ids(23,68,69,71,124,283,284,285,286,287,288,311) from the database
        $catalogArea1 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea1->save();

        $catalogArea2 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea2->save();

        $catalogArea3 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea3->save();

        $catalogArea4 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea4->save();

        $catalogArea5 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea5->save();

        $catalogArea6 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 2,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea6->save();

        $catalogArea7 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea7->save();

        $catalogArea8 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea8->save();

        $catalogArea9 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea9->save();

        $catalogArea10 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea10->save();

        $catalogArea11 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea11->save();

        $catalogArea12 = CatalogArea::factory()->create([
            'catalog_id' => $this->catalog->id,
            'catalog_type_id' => 1,
            'geometry' => $this->parcelGeom
        ]);
        $catalogArea12->save();

        $this->catalogAreas = [$catalogArea1, $catalogArea2, $catalogArea3, $catalogArea4, $catalogArea5, $catalogArea6, $catalogArea7, $catalogArea8, $catalogArea9, $catalogArea10, $catalogArea11, $catalogArea12];

        //take the CatalogAreas geometries from Stubs folder
        $areasPath = __DIR__ . '/Stubs/CatalogAreas.json';
        $areasGeojson = json_decode(file_get_contents($areasPath), true)["areas"];

        //assign the geometries to the catalog areas
        // for ($i = 0; $i < count($this->catalogAreas); $i++) {
        // $areaGeojson_content = json_encode($areasGeojson[$i]['geometry']);
        // $sql = "SELECT ST_AsText(ST_Force2D(ST_CollectionExtract(ST_Polygonize(ST_GeomFromGeoJSON('" . $areaGeojson_content . "')), 3))) As wkt";
        // $areaGeom = DB::select($sql)[0]->wkt;
        // $this->catalogAreas[$i]->geometry = $areaGeom;
        // $this->catalogAreas[$i]->save();
        // }
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
     * Test computeCatalogEstimate method throws exception for invalid catalog id.
     *
     * @return void
     */
    public function test_compute_catalog_estimate_method_throws_exception_for_invalid_catalog_id()
    {
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

        $this->expectException(ModelNotFoundException::class);
        $this->parcel->computeCatalogEstimate(999);
    }



    /**
     * Test computeCatalogEstimate method return array with correct keys.
     * 
     * @return void
     */
    public function test_compute_catalog_estimate_method_return_array_with_correct_json_structure()
    {

        //set up the environment
        $this->createData();

        // Check if the command exists
        $this->assertTrue(class_exists(\App\Console\Commands\EstimateByCatalog::class));

        // Compute the catalog estimate
        $result = $this->parcel->computeCatalogEstimate($this->catalog->id);

        // Check if the result is an array
        $this->assertIsArray($result);

        // Check if the result has the expected keys
        $this->assertArrayHasKey('interventions', $result);
        $this->assertArrayHasKey('maintenance', $result);
        $this->assertArrayHasKey('general', $result);

        // Check if the 'interventions' array has the expected keys
        $interventions = $result['interventions'];
        $this->assertArrayHasKey('items', $interventions);
        $this->assertArrayHasKey('info', $interventions);

        // Check if the 'items' array has the expected structure
        $items = $interventions['items'];
        $this->assertIsArray($items);
        if (count($items) > 0) {
            foreach ($items as $item) {
                $this->assertArrayHasKey('code', $item);
                $this->assertArrayHasKey('area', $item);
                $this->assertArrayHasKey('unit_price', $item);
                $this->assertArrayHasKey('price', $item);
            }
        }
        // Check if the 'info' array has the expected keys
        $info = $interventions['info'];
        $this->assertArrayHasKey('intervention_area', $info);
        $this->assertArrayHasKey('intervention_price', $info);
        $this->assertArrayHasKey('supervision_price', $info);
        $this->assertArrayHasKey('overhead_price', $info);
        $this->assertArrayHasKey('business_profit_price', $info);
        $this->assertArrayHasKey('intervention_certification', $info);
        $this->assertArrayHasKey('total_intervention_certificated_price', $info);
        $this->assertArrayHasKey('team_price', $info);
        $this->assertArrayHasKey('platform_maintenance_price', $info);
        $this->assertArrayHasKey('total_intervention_gross_price', $info);
        $this->assertArrayHasKey('total_intervention_net_price', $info);
        $this->assertArrayHasKey('total_intervention_vat', $info);
        $this->assertArrayHasKey('intervention_gross_price_per_area', $info);
    }
}
