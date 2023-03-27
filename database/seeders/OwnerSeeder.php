<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\CadastralParcel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Owner::factory(10)->create();

        foreach (Owner::all() as $owner) {
            $owner->cadastralParcels->attach(
                CadastralParcel::inRandomOrder()->first()
            );
        }
    }
}
