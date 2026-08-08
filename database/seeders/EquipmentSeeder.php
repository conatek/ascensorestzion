<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Site;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::all();
        $code  = 1;

        $brands  = ['Otis', 'Mitsubishi', 'Hyundai', 'Schindler', 'ThyssenKrupp', 'KONE'];
        $models  = ['Gen2', 'NexWay', 'YOLLEO', 'S3000', '5400', 'MonoSpace'];
        $types   = ['ascensor', 'ascensor', 'ascensor', 'escalera_electrica', 'montacargas'];
        $statuses = ['activo', 'activo', 'activo', 'fuera_servicio'];
        $contracts = ['mantenimiento', 'integral', 'garantia', 'por_llamada'];

        foreach ($sites as $site) {
            $count = rand(2, 3);
            for ($i = 0; $i < $count; $i++) {
                $brandIdx = array_rand($brands);
                Equipment::create([
                    'site_id'                    => $site->id,
                    'internal_code'              => sprintf('TZ-%d-%04d', date('Y'), $code++),
                    'equipment_type'             => $types[array_rand($types)],
                    'brand'                      => $brands[$brandIdx],
                    'model'                      => $models[$brandIdx],
                    'serial_number'              => 'SN-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'capacity_kg'                => [630, 1000, 1600, 2000][array_rand([630, 1000, 1600, 2000])],
                    'capacity_persons'           => [8, 13, 21, 26][array_rand([8, 13, 21, 26])],
                    'stops'                      => rand(4, 20),
                    'speed_mps'                  => [1.0, 1.5, 1.75, 2.5][array_rand([1.0, 1.5, 1.75, 2.5])],
                    'installation_date'          => now()->subYears(rand(1, 15))->subMonths(rand(0, 11))->toDateString(),
                    'contract_type'              => $contracts[array_rand($contracts)],
                    'contract_start'             => now()->subMonths(rand(1, 12))->toDateString(),
                    'contract_end'               => now()->addMonths(rand(1, 24))->toDateString(),
                    'maintenance_frequency_days' => 30,
                    'status'                     => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
