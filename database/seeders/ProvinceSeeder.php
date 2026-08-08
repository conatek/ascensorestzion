<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Province::create(['name' => 'Amazonas']);
        Province::create(['name' => 'Antioquia']);
        Province::create(['name' => 'Arauca']);
        Province::create(['name' => 'Archipiélago de San Andrés']);
        Province::create(['name' => 'Atlántico']);
        Province::create(['name' => 'Bogotá D.C.']);
        Province::create(['name' => 'Bolivar']);
        Province::create(['name' => 'Boyacá']);
        Province::create(['name' => 'Caldas']);
        Province::create(['name' => 'Caquetá']);
        Province::create(['name' => 'Casanare']);
        Province::create(['name' => 'Cauca']);
        Province::create(['name' => 'Cesar']);
        Province::create(['name' => 'Chocó']);
        Province::create(['name' => 'Córdoba']);
        Province::create(['name' => 'Cundinamarca']);
        Province::create(['name' => 'Guainía']);
        Province::create(['name' => 'Guaviare']);
        Province::create(['name' => 'Huila']);
        Province::create(['name' => 'La Guajira']);
        Province::create(['name' => 'Magdalena']);
        Province::create(['name' => 'Meta']);
        Province::create(['name' => 'Nariño']);
        Province::create(['name' => 'Norte de Santander']);
        Province::create(['name' => 'Putumayo']);
        Province::create(['name' => 'Quindío']);
        Province::create(['name' => 'Risaralda']);
        Province::create(['name' => 'Santander']);
        Province::create(['name' => 'Sucre']);
        Province::create(['name' => 'Tolima']);
        Province::create(['name' => 'Valle del Cauca']);
        Province::create(['name' => 'Vaupés']);
        Province::create(['name' => 'Vichada']);
    }
}
