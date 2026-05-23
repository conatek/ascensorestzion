<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $tzion = Company::where('slug', 'ascensorestzion')->first();

        if (!$tzion) return;

        Card::create([
            'company_id'      => $tzion->id,
            'first_name'      => 'Antonio',
            'last_name'       => 'Contreras',
            'slug'            => 'antoniocontreras',
            'job_title'       => 'Gerente General',
            'mobile_phone'    => '+57 302 311 9169',
            'whatsapp'        => '+573023119169',
            'email'           => 'operaciones@ascensorestzion.com',
            'whatsapp_message' => 'Hola, me gustaría obtener información sobre sus servicios de mantenimiento.',
            'description'     => 'Especialista en mantenimiento y modernización de equipos de transporte vertical.',
            'is_active'       => true,
        ]);

        Card::create([
            'company_id'      => $tzion->id,
            'first_name'      => 'Sandra',
            'last_name'       => 'Mejía',
            'slug'            => 'sandramejia',
            'job_title'       => 'Coordinadora de Servicio',
            'mobile_phone'    => '+57 300 401 9483',
            'whatsapp'        => '+573004019483',
            'email'           => 'coordinacion@ascensorestzion.com',
            'whatsapp_message' => 'Hola, necesito coordinar un servicio técnico.',
            'description'     => 'Coordinación y seguimiento de servicios técnicos preventivos y correctivos.',
            'is_active'       => true,
        ]);
    }
}
