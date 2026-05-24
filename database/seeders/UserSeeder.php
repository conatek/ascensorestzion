<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tzion = Company::where('slug', 'ascensorestzion')->first();

        // ── Usuarios internos (Ascensores Tzion) ──

        $master = User::create([
            'company_id' => $tzion->id,
            'name'       => 'Antonio Contreras',
            'email'      => 'master@ascensorestzion.com',
            'password'   => Hash::make('password'),
            'phone'      => '+57 302 311 9169',
            'active'     => true,
        ]);
        $master->assignRole('master');
        $tzion->update(['user_id' => $master->id]);

        $coordinator = User::create([
            'company_id' => $tzion->id,
            'name'       => 'Sandra Mejía',
            'email'      => 'coordinador@ascensorestzion.com',
            'password'   => Hash::make('password'),
            'phone'      => '+57 300 401 9483',
            'active'     => true,
        ]);
        $coordinator->assignRole('coordinator');

        $tech1 = User::create([
            'company_id'      => $tzion->id,
            'name'            => 'Juan David Ríos',
            'email'           => 'tecnico1@ascensorestzion.com',
            'password'        => Hash::make('password'),
            'phone'           => '+57 310 555 0001',
            'document_type'   => 'CC',
            'document_number' => '1.017.123.456',
            'active'          => true,
        ]);
        $tech1->assignRole('technician');

        $tech2 = User::create([
            'company_id'      => $tzion->id,
            'name'            => 'Andrés Felipe Cardona',
            'email'           => 'tecnico2@ascensorestzion.com',
            'password'        => Hash::make('password'),
            'phone'           => '+57 310 555 0002',
            'document_type'   => 'CC',
            'document_number' => '1.035.789.012',
            'active'          => true,
        ]);
        $tech2->assignRole('technician');

        // ── Usuarios externos (admin de clientes) ──

        $clients = Client::all();

        foreach ($clients as $client) {
            $slug  = strtolower(str_replace(' ', '', substr($client->business_name, 0, 10)));
            $admin = User::create([
                'client_id' => $client->id,
                'name'      => $client->contact_name ?? 'Admin ' . $client->business_name,
                'email'     => $client->contact_email ?? "admin@{$slug}.com",
                'password'  => Hash::make('password'),
                'phone'     => $client->contact_phone,
                'active'    => true,
            ]);
            $admin->assignRole('admin');
        }
    }
}
