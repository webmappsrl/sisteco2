<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Console\Command;

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

        if ($this->argument('subject') == 'owners') {
            $this->importOwners($ownersData);
        } elseif ($this->argument('subject') == 'users') {
            $this->importUsers($usersData);
        } else {
            $this->importUsers($usersData);
            $this->importOwners($ownersData);
        }
    }
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
            }
        }

        $this->info('Done! Imported ' . $count . ' owners.');
    }
}
