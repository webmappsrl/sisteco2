<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Support\Arr;
use App\Models\CadastralParcel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Array_;

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
    protected $description = 'Import data from Sisteco. Usage: php artisan sisteco2:import {subject} where subject is either "users" or "owners". If no subject is specified, both users and owners will be imported.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $usersData = json_decode(file_get_contents('http://sis-te.com/api/export/users'), true);
        $ownersData = json_decode(file_get_contents('http://sis-te.com/api/export/owners'), true);


        switch ($this->argument('subject')) {
            case 'owners':
                $this->importOwners($ownersData);
                break;
            case 'users':
                $this->importUsers($usersData);
                break;
            case $this->argument('subject') != 'owners' && $this->argument('subject') != 'users':
                $this->error('Invalid subject. Usage: php artisan sisteco2:import {subject} where subject is either "users" or "owners". If no subject is specified, both users and owners will be imported.');
                break;
            default:
                $this->importUsers($usersData);
                $this->importOwners($ownersData);
                break;
        }
    }

    /**
     * Import users
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
                if (User::where('email', $user['email'])->exists()) {
                    $this->info('User ' . $user['name'] . ' already exists, skipping...');
                    continue;
                }
                $count++;
                $this->info('Importing user ' . $user['name'] . ' (' . $count . '/' . count($users));
                User::updateOrCreate([
                    'email' => $user['email'],
                ], [
                    'name' => $user['name'],
                    'password' => $user['password'],
                ]);
            }
        }

        $this->info('Done! Imported ' . $count . ' users.');
    }

    /**
     * Import owners
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
                if (Owner::where('sisteco_legacy_id', $owner['id'])->exists()) {
                    $this->info('Owner ' . $owner['last_name'] .  ' already exists, skipping...');
                    continue;
                }
                $count++;
                $this->info('Importing owner ' . $owner['last_name'] . ' (' . $count . '/' . count($owners));
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

                // import cadastral parcels related to the owner
                $this->importCadastralParcels($owner['cadastral_parcels'], $owner['last_name']);
            }
        }
        $this->info('Done! Imported ' . $count . ' owners.');
    }

    /**
     * Import cadastral parcels
     * @param $data
     * 
     * @return void
     */
    private function importCadastralParcels(array $data, $owner = null)
    {
        $this->info('Importing ' . count($data) . ' cadastral parcels' . ($owner ? ' for ' . $owner : ''));
        foreach ($data as $element) {

            $parcel = json_decode(file_get_contents('http://sis-te.com/api/export/cadastral_parcel/' . $element), true);
            $parcelData = $parcel['data'];
            if (CadastralParcel::where('sisteco_legacy_id', $parcelData['id'])->exists()) {
                $this->info('Cadastral parcel ' . $parcelData['id'] . ' already exists, skipping...');
                continue;
            }
            $this->info('Importing cadastral parcel ' . $parcelData['id']);
            if ($parcelData['geometry']) {
                $geojson_content = json_encode($parcelData['geometry']);
                $sql = "SELECT ST_AsText(ST_Force2D(ST_CollectionExtract(ST_Polygonize(ST_GeomFromGeoJSON('" . $geojson_content . "')), 3))) As wkt";
                $parcelGeometry = DB::select($sql)[0]->wkt;
            } else {
                $parcelGeometry = null;
            }

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
        }
        $this->info('Done! Imported ' . count($data) . ' cadastral parcels.');
    }
}
