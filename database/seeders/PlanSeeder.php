<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(
            ['name' => 'guest'],
            [
                'display_name'        => 'Gratis',
                'price_monthly'       => 0,
                'price_yearly'        => 0,
                'max_cards'           => 1,
                'max_products'        => 3,
                'max_services'        => 3,
                'available_templates' => ['modern', 'classic'],
                'show_watermark'      => true,
                'features'            => [],
                'is_active'           => true,
                'sort_order'          => 0,
            ]
        );

        Plan::firstOrCreate(
            ['name' => 'basico'],
            [
                'display_name'        => 'Básico',
                'price_monthly'       => 49900,
                'price_yearly'        => 499000,
                'max_cards'           => 5,
                'max_products'        => 15,
                'max_services'        => 15,
                'available_templates' => null,
                'show_watermark'      => false,
                'features'            => ['qr_personalizado' => true],
                'is_active'           => true,
                'sort_order'          => 1,
            ]
        );

        Plan::firstOrCreate(
            ['name' => 'pro'],
            [
                'display_name'        => 'Pro',
                'price_monthly'       => 99900,
                'price_yearly'        => 999000,
                'max_cards'           => 20,
                'max_products'        => null,
                'max_services'        => null,
                'available_templates' => null,
                'show_watermark'      => false,
                'features'            => ['qr_personalizado' => true, 'dominio_propio' => true],
                'is_active'           => true,
                'sort_order'          => 2,
            ]
        );
    }
}
