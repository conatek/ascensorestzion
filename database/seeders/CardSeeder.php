<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $conatek = Company::where('slug', 'conatek')->first();
        $muyHumano = Company::where('slug', 'muy-humano')->first();

        // Tarjetas para CONATEK
        if ($conatek) {
            Card::create([
                'company_id' => $conatek->id,
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'slug' => 'carlos-mendoza',
                'job_title' => 'Director General',
                'mobile_phone' => '+58 412 555 1234',
                'whatsapp' => '+58412555124',
                'email' => 'carlos.mendoza@conatek.com',
                'linkedin' => 'https://www.linkedin.com/in/carlosmendoza',
                'whatsapp_message' => 'Hola, me gustaría obtener más información sobre sus servicios.',
                'description' => 'Líder con más de 15 años de experiencia en el sector tecnológico.',
                'is_active' => true,
            ]);

            Card::create([
                'company_id' => $conatek->id,
                'first_name' => 'María',
                'last_name' => 'González',
                'slug' => 'maria-gonzalez',
                'job_title' => 'Gerente de Proyectos',
                'mobile_phone' => '+58 414 555 5678',
                'whatsapp' => '+584145555678',
                'email' => 'maria.gonzalez@conatek.com',
                'linkedin' => 'https://www.linkedin.com/in/mariagonzalez',
                'whatsapp_message' => 'Hola, estoy interesado en coordinar una reunión.',
                'description' => 'Especialista en gestión de proyectos de transformación digital.',
                'is_active' => true,
            ]);
        }

        // Tarjetas para MUY HUMANO
        if ($muyHumano) {
            Card::create([
                'company_id' => $muyHumano->id,
                'first_name' => 'Ana',
                'last_name' => 'Rodríguez',
                'slug' => 'ana-rodriguez',
                'job_title' => 'Fundadora y CEO',
                'mobile_phone' => '+58 416 555 9012',
                'whatsapp' => '+584165559012',
                'email' => 'ana.rodriguez@muyhumano.com',
                'linkedin' => 'https://www.linkedin.com/in/anarodriguez',
                'whatsapp_message' => 'Hola, me interesa conocer más sobre Muy Humano.',
                'description' => 'Emprendedora apasionada por crear conexiones humanas.',
                'is_active' => true,
            ]);

            Card::create([
                'company_id' => $muyHumano->id,
                'first_name' => 'Pedro',
                'last_name' => 'Sánchez',
                'slug' => 'pedro-sanchez',
                'job_title' => 'Director Creativo',
                'mobile_phone' => '+58 424 555 3456',
                'whatsapp' => '+584245553456',
                'email' => 'pedro.sanchez@muyhumano.com',
                'linkedin' => 'https://www.linkedin.com/in/pedrosanchez',
                'whatsapp_message' => 'Hola, quisiera hablar sobre un proyecto creativo.',
                'description' => 'Diseñador con enfoque en experiencias memorables.',
                'is_active' => true,
            ]);
        }
    }
}
