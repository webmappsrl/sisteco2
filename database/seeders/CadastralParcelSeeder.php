<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\CadastralParcel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CadastralParcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CadastralParcel::factory(10)->create();

        foreach (CadastralParcel::all() as $cadastralParcel) {
            $cadastralParcel->owners()->attach(
                Owner::inRandomOrder()->first()
            );
        }
    }
}
