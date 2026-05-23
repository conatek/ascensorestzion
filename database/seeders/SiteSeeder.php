<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $oviedo    = Client::where('nit', '890.903.456-1')->first();
        $torreNorte = Client::where('nit', '901.234.567-8')->first();
        $hospital  = Client::where('nit', '890.901.234-5')->first();

        // Centro Comercial Oviedo — 2 sedes
        Site::create([
            'client_id'           => $oviedo->id,
            'name'                => 'Torre Administrativa',
            'address'             => 'Carrera 43A # 6 Sur-15, Torre Admin',
            'city'                => 'Medellín',
            'department'          => 'Antioquia',
            'contact_name_onsite' => 'Jorge Palacio',
            'contact_phone_onsite' => '+57 300 555 1001',
        ]);

        Site::create([
            'client_id'           => $oviedo->id,
            'name'                => 'Zona Comercial Principal',
            'address'             => 'Carrera 43A # 6 Sur-15, Zona Comercial',
            'city'                => 'Medellín',
            'department'          => 'Antioquia',
            'contact_name_onsite' => 'Andrea Muñoz',
            'contact_phone_onsite' => '+57 300 555 1002',
        ]);

        // Torre Norte — 2 sedes
        Site::create([
            'client_id'           => $torreNorte->id,
            'name'                => 'Torre A',
            'address'             => 'Calle 10 # 43-12, Torre A',
            'city'                => 'Medellín',
            'department'          => 'Antioquia',
            'contact_name_onsite' => 'Fernando Gil',
            'contact_phone_onsite' => '+57 302 555 2001',
        ]);

        Site::create([
            'client_id'           => $torreNorte->id,
            'name'                => 'Torre B',
            'address'             => 'Calle 10 # 43-12, Torre B',
            'city'                => 'Medellín',
            'department'          => 'Antioquia',
            'contact_name_onsite' => 'Diana Herrera',
            'contact_phone_onsite' => '+57 302 555 2002',
        ]);

        // Hospital — 2 sedes
        Site::create([
            'client_id'           => $hospital->id,
            'name'                => 'Edificio Principal',
            'address'             => 'Calle 64 # 51D-154, Edificio Principal',
            'city'                => 'Medellín',
            'department'          => 'Antioquia',
            'contact_name_onsite' => 'Ricardo Salazar',
            'contact_phone_onsite' => '+57 300 555 3001',
        ]);

        Site::create([
            'client_id'           => $hospital->id,
            'name'                => 'Bloque Hospitalario Sur',
            'address'             => 'Calle 64 # 51D-154, Bloque Sur',
            'city'                => 'Medellín',
            'department'          => 'Antioquia',
            'contact_name_onsite' => 'Patricia Gómez',
            'contact_phone_onsite' => '+57 300 555 3002',
        ]);
    }
}
