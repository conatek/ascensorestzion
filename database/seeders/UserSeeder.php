<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $conatek   = Company::where('slug', 'conatek')->first();
        $muyhumano = Company::where('slug', 'muy-humano')->first();

        // ── CONATEK ──────────────────────────────────────────────
        $master1 = User::create([
            'company_id' => $conatek->id,
            'name'       => 'Antonio Contreras',
            'email'      => 'master@conatek.com',
            'password'   => Hash::make('password'),
        ]);
        $master1->assignRole('Master');
        $conatek->update(['user_id' => $master1->id]);

        $admin1 = User::create([
            'company_id' => $conatek->id,
            'name'       => 'Admin Conatek',
            'email'      => 'admin@conatek.com',
            'password'   => Hash::make('password'),
        ]);
        $admin1->assignRole('Admin');

        // ── MUY HUMANO ───────────────────────────────────────────
        $master2 = User::create([
            'company_id' => $muyhumano->id,
            'name'       => 'Master Muy Humano',
            'email'      => 'master@muyhumano.com',
            'password'   => Hash::make('password'),
        ]);
        $master2->assignRole('Master');
        $muyhumano->update(['user_id' => $master2->id]);

        $admin2 = User::create([
            'company_id' => $muyhumano->id,
            'name'       => 'Admin Muy Humano',
            'email'      => 'admin@muyhumano.com',
            'password'   => Hash::make('password'),
        ]);
        $admin2->assignRole('Admin');

        $guest2 = User::create([
            'company_id' => $muyhumano->id,
            'name'       => 'Guest Muy Humano',
            'email'      => 'guest@muyhumano.com',
            'password'   => Hash::make('password'),
        ]);
        $guest2->assignRole('Guest');

        // ── CONATEK (guest) ───────────────────────────────────────
        $guest1 = User::create([
            'company_id' => $conatek->id,
            'name'       => 'Guest Conatek',
            'email'      => 'guest@conatek.com',
            'password'   => Hash::make('password'),
        ]);
        $guest1->assignRole('Guest');
    }
}
