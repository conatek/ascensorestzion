<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'CONATEK',
            'slug' => 'conatek',
            'address' => 'Av. Principal #123, Centro Empresarial, Piso 5',
            'web' => 'https://www.conatek.com',
            'my_business' => 'https://g.page/conatek',
            'facebook' => 'https://www.facebook.com/conatek',
            'instagram' => 'https://www.instagram.com/conatek',
            'twitter' => 'https://twitter.com/conatek',
            'youtube' => 'https://www.youtube.com/@conatek',
        ]);

        Company::create([
            'name' => 'MUY HUMANO',
            'slug' => 'muy-humano',
            'address' => 'Calle Las Flores #456, Zona Comercial',
            'web' => 'https://www.muyhumano.com',
            'my_business' => 'https://g.page/muyhumano',
            'facebook' => 'https://www.facebook.com/muyhumano',
            'instagram' => 'https://www.instagram.com/muyhumano',
        ]);
    }
}
