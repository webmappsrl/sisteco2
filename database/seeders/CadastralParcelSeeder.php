<?php

namespace Database\Seeders;

use App\Models\CadastralParcel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CadastralParcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CadastralParcel::factory(10)->create();
    }
}
