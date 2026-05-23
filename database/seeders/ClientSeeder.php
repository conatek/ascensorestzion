<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::create([
            'business_name' => 'Centro Comercial Oviedo',
            'nit'           => '890.903.456-1',
            'contact_name'  => 'Laura Betancur',
            'contact_email' => 'admin@ccoviedo.com',
            'contact_phone' => '+57 604 321 5000',
            'address'       => 'Carrera 43A # 6 Sur-15',
            'city'          => 'Medellín',
            'department'    => 'Antioquia',
            'active'        => true,
        ]);

        Client::create([
            'business_name' => 'Edificio Torre Norte P.H.',
            'nit'           => '901.234.567-8',
            'contact_name'  => 'Carlos Restrepo',
            'contact_email' => 'administracion@torrenorte.co',
            'contact_phone' => '+57 604 444 2200',
            'address'       => 'Calle 10 # 43-12',
            'city'          => 'Medellín',
            'department'    => 'Antioquia',
            'active'        => true,
        ]);

        Client::create([
            'business_name' => 'Hospital San Vicente Fundación',
            'nit'           => '890.901.234-5',
            'contact_name'  => 'María Fernanda López',
            'contact_email' => 'mantenimiento@sanvicente.org',
            'contact_phone' => '+57 604 263 5600',
            'address'       => 'Calle 64 # 51D-154',
            'city'          => 'Medellín',
            'department'    => 'Antioquia',
            'active'        => true,
        ]);
    }
}
