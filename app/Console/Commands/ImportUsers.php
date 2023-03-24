<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco2:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from Sisteco';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $usersData = json_decode(file_get_contents('http://sis-te.com/api/export/users'), true);
        $this->importUsers($usersData);
    }
    private function importUsers($data)
    {
        $count = 0;
        $this->info('Importing ' . count($data) . ' users...');
        foreach ($data as $users) {
            foreach ($users as $user) {
                if (User::where('email', $user['email'])->exists()) {
                    $this->info('User ' . $user['name'] . ' already exists, skipping...');
                    continue;
                }
                $count++;
                $this->info('Importing user ' . $user['name'] . ' (' . $count . '/' . count($data));
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
}
