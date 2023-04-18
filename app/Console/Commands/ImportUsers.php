<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Owner;
use App\Enums\UserRole;
use App\Models\Catalog;
use App\Models\CatalogArea;
use App\Models\CatalogType;
use App\Models\CadastralParcel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco2:import{subject?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from Sisteco. Usage: php artisan sisteco2:import {subject} where subject is (users, owners, catalogs). If no subject is specified, all subjects will be imported.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //base API URL
        $sistecoApi = 'http://sis-te.com/api/export/';

        //defining API endpoints
        $usersApi = $sistecoApi . 'users';
        $ownersApi = $sistecoApi . 'owners';
        $catalogsApi = $sistecoApi . 'catalogs';
        $catalogTypesApi = $sistecoApi . 'catalog_types';
        $catalogAreasApi = $sistecoApi . 'catalog_areas';


        //getting data from API
        $usersData = json_decode(file_get_contents($usersApi), true);
        $ownersData = json_decode(file_get_contents($ownersApi), true);
        $catalogData = json_decode(file_get_contents($catalogsApi), true);
        $catalogTypeData = json_decode(file_get_contents($catalogTypesApi), true);
        $catalogAreaData = json_decode(file_get_contents($catalogAreasApi), true);


        //defining command behavior
        switch ($this->argument('subject')) {
            case 'owners':
                $this->importOwners($ownersData);
                break;
            case 'users':
                $this->importUsers($usersData);
                break;
            case 'catalogs':
                $this->importCatalogs($catalogData, $catalogTypeData, $catalogAreaData);
                break;
            case $this->argument('subject') != 'owners' && $this->argument('subject') != 'users' && $this->argument('subject') != 'catalogs':
                $this->error('Invalid subject. Usage: php artisan sisteco2:import {subject} where subject is (users, owners, catalogs). If no subject is specified, all subjects will be imported.');
                break;
            default:
                $this->importUsers($usersData);
                $this->importOwners($ownersData);
                $this->importCatalogs($catalogData, $catalogTypeData, $catalogAreaData);
                break;
        }
    }

    /**
     * Import users function
     * @param $data
     * 
     * @return void
     */
    private function importUsers($data)
    {
        $count = 0;

        foreach ($data as $users) {
            $this->info('Importing ' . count($users) . ' users...');
            foreach ($users as $user) {

                //check if user already exists
                // if (User::where('email', $user['email'])->exists()) {
                //     $this->info('User ' . $user['name'] . ' already exists, skipping...');
                //     continue;
                // }
                $count++;
                $this->info('Importing user ' . $user['name'] . ' (' . $count . '/' . count($users));

                //create user
                User::updateOrCreate([
                    'email' => $user['email'],
                ], [
                    'name' => $user['name'],
                    'password' => $user['password'],
                ]);
            }
        }
        //set the role of the user with email = team@webmapp.it to admin
        $webmapp = User::where('email', 'team@webmapp.it')->first();
        $webmapp->roles = UserRole::Admin;
        $webmapp->save();

        $this->info('Done! Imported ' . $count . ' users.');
    }

    /**
     * Import owners and related cadastral parcels function
     * @param $data
     * 
     * @return void
     */
    private function importOwners($data)
    {
        $count = 0;
        foreach ($data as $owners) {
            $this->info('Importing ' . count($owners) . ' owners...');
            foreach ($owners as $owner) {
                //check if owner already exists
                if (Owner::where('sisteco_legacy_id', $owner['id'])->exists()) {
                    $this->info('Owner ' . $owner['last_name'] .  ' already exists, skipping...');
                    continue;
                }
                $count++;
                $this->info('Importing owner ' . $owner['last_name'] . ' (' . $count . '/' . count($owners) . ')');
                //create owner
                Owner::updateOrCreate([
                    'sisteco_legacy_id' => $owner['id'],
                ], [
                    'first_name' => $owner['first_name'],
                    'last_name' => $owner['last_name'],
                    'email' => $owner['email'],
                    'business_name' => $owner['business_name'],
                    'vat_number' => $owner['vat_number'],
                    'fiscal_code' => $owner['fiscal_code'],
                    'phone' => $owner['phone'],
                    'addr:street' => $owner['addr:street'],
                    'addr:housenumber' => $owner['addr:housenumber'],
                    'addr:city' => $owner['addr:city'],
                    'addr:postcode' => $owner['addr:postcode'],
                    'addr:province' => $owner['addr:province'],
                    'addr:locality' => $owner['addr:locality'],
                ]);

                // call cadastral parcels import function to import related parcels for each owner
                $this->importCadastralParcels($owner['cadastral_parcels'], $owner);
            }
        }
        $this->info('Done! Imported ' . $count . ' owners.');
    }

    /**
     * Import cadastral parcels function
     * @param $data
     * 
     * @return void
     */
    private function importCadastralParcels(array $data, $ownerData = null)
    {
        $this->info('Importing ' . count($data) . ' cadastral parcels' . ($ownerData ? ' for ' . $ownerData['last_name'] : ''));
        foreach ($data as $element) {

            // get parcel data from API
            $parcel = json_decode(file_get_contents('http://sis-te.com/api/export/cadastral_parcel/' . $element), true);
            $parcelData = $parcel['data'];
            $this->info('Importing cadastral parcel ' . $parcelData['id']);

            // get parcel geometry
            if ($parcelData['geometry']) {
                $geojson_content = json_encode($parcelData['geometry']);

                if ($parcelData['geometry']['type'] === 'MultiPolygon') {
                    $sql = "SELECT ST_AsText(ST_Force2D(ST_CollectionExtract(ST_Polygonize(ST_GeomFromGeoJSON('" . $geojson_content . "')), 3))) As wkt";
                } else {
                    $sql = "SELECT ST_AsText(ST_Force2D(ST_Multi(ST_GeomFromGeoJSON('" . $geojson_content . "')))) As wkt";
                }
                $parcelGeometry = DB::select($sql)[0]->wkt;
            } else {
                $parcelGeometry = null;
            }
            // create parcel
            CadastralParcel::updateOrCreate(
                ['sisteco_legacy_id' => $parcelData['id']],
                [
                    'code' => $parcelData['code'],
                    'municipality' => $parcelData['municipality'],
                    'estimated_value' => $parcelData['estimated_value'],
                    'average_slope' => $parcelData['average_slope'],
                    'meter_min_distance_road' => intval($parcelData['meter_min_distance_road']),
                    'meter_min_distance_path' => intval($parcelData['meter_min_distance_path']),
                    'square_meter_surface' => floatval($parcelData['square_meter_surface']),
                    'slope' => $parcelData['slope'],
                    'way' => $parcelData['way'],
                    'catalog_estimate' => $parcelData['catalog_estimate'],
                    'geometry' => $parcelGeometry,

                ]
            );

            //attach parcel to owner
            if ($ownerData) {
                $owner = Owner::where('sisteco_legacy_id', $ownerData['id'])->first();
                $parcel = CadastralParcel::where('sisteco_legacy_id', $parcelData['id'])->first();
                $owner->cadastralParcels()->attach($parcel);
            }
        }
        $this->info('Done! Imported ' . count($data) . ' cadastral parcels.');
    }

    /**
     * Import catalogs, types and areas
     * @param $data
     * 
     * @return void
     */

    private function importCatalogs($catalogs, $types, $areas)
    {
        $this->catalogs($catalogs);
        $this->catalogTypes($types);
        $this->catalogAreas($areas);

        $this->info('Everything imported successfully');
    }


    /**
     * Import catalogs function
     * @param $data
     * 
     * @return void
     */
    private function catalogs($data)
    {
        $count = 0;
        foreach ($data as $catalogsData) {
            $this->info('Importing ' . count($catalogsData) . ' catalogs...');
            foreach ($catalogsData as $catalog) {

                //check if catalog already exists
                if (Catalog::where('sisteco_legacy_id', $catalog['id'])->exists()) {
                    $this->info('Catalog ' . $catalog['name'] .  ' already exists, skipping...');
                    continue;
                }
                $count++;
                $this->info('Importing catalog ' . $catalog['name']);

                //create catalog
                Catalog::updateOrCreate(
                    [
                        'sisteco_legacy_id' => $catalog['id'],
                    ],
                    [
                        'name' => $catalog['name'],
                        'description' => $catalog['description']
                    ]
                );
            }
        }
        $this->info('Done! Imported ' . $count . ' catalogs.');
    }


    /**
     * Import catalog types function
     * @param $data
     * 
     * @return void
     */
    private function catalogTypes($data)
    {
        foreach ($data as $typeData) {
            $this->info('Importing ' . count($typeData) . ' catalogs type ');
            foreach ($typeData as $type) {

                //check if catalog type already exists
                if (CatalogType::where('sisteco_legacy_id', $type['id'])->exists()) {
                    $this->info('Catalog type ' . $type['name'] .  ' already exists, skipping...');
                    continue;
                }
                $this->info('Importing catalog type ' . $type['name']);
                $catalog = Catalog::where('sisteco_legacy_id', $type['catalog_id'])->first();

                //create catalog type
                CatalogType::updateOrCreate(
                    [
                        'sisteco_legacy_id' => $type['id'],
                    ],
                    [
                        'name' => $type['name'],
                        'catalog_id' => $catalog->id,
                        'cod_int' => $type['code_int'],
                        'prices' => $type['prices'],
                    ]
                );
            }
        }
        $this->info('Done! Imported ' . count($typeData) . ' catalogs type.');
    }

    /**
     * Import catalog areas function
     * @param $data
     * 
     * @return void
     */
    private function catalogAreas($data)
    {
        foreach ($data as $areaData) {
            $this->info('Importing ' . count($areaData) . ' catalogs area ');
            foreach ($areaData as $area) {

                //check if catalog area already exists
                if (CatalogArea::where('sisteco_legacy_id', $area['id'])->exists()) {
                    $this->info('Catalog area ' . $area['id'] .  ' already exists, skipping...');
                    continue;
                }
                $this->info('Importing catalog area with id: ' . $area['id']);
                $geojson_content = json_encode($area['geometry']);
                $sql = "SELECT ST_AsText(ST_Force2D(ST_CollectionExtract(ST_Polygonize(ST_GeomFromGeoJSON('" . $geojson_content . "')), 3))) As wkt";
                $areaGeometry = DB::select($sql)[0]->wkt;
                $catalogType = CatalogType::where('sisteco_legacy_id', $area['catalog_type_id'])->first();

                //create catalog area
                CatalogArea::updateOrCreate(
                    [
                        'sisteco_legacy_id' => $area['id'],
                    ],
                    [
                        'catalog_id' => $area['catalog_id'],
                        'catalog_type_id' => $catalogType->id,
                        'geometry' => $areaGeometry,
                    ]
                );
            }
        }
        $this->info('Done! Imported ' . count($areaData) . ' catalogs area.');
    }
}
