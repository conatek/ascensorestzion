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
            CompanySeeder::class,
            ClientSeeder::class,
            SiteSeeder::class,
            EquipmentSeeder::class,
            CatalogSeeder::class,
            CardSeeder::class,
            UserSeeder::class,
            ServiceReportSeeder::class,
            ScheduleSettingSeeder::class,
        ]);
    }
}
