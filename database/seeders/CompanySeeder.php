<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Ascensores Tzion',
            'slug' => 'ascensorestzion',
            'address' => 'Carrera 78 # 41-32, Laureles Lorena, Medellín - Antioquia',
            'web' => 'https://www.ascensorestzion.com',
            'facebook' => 'https://www.facebook.com/ascensorestzion',
            'instagram' => 'https://www.instagram.com/ascensorestzion',
        ]);
    }
}
