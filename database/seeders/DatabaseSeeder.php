<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            CitySeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            PlanSeeder::class,
            CompanySeeder::class,
            CardSeeder::class,
            UserSeeder::class,
        ]);
    }
}
