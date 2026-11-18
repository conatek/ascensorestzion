<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\SupplyItem;
use App\Services\SupplierOfferService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Proveedores de EJEMPLO, con sedes, asesores, productos y precios, para que el
 * equipo entienda el modulo. Son ficticios (NIT, telefonos y correos inventados)
 * y van marcados con `is_example`: en pantalla llevan la etiqueta "Ejemplo" y se
 * borran desde la propia aplicacion a medida que se registran los reales.
 *
 * Idempotente: se puede correr varias veces; no duplica ni resucita lo borrado.
 *
 *   php artisan db:seed --class=SupplierExampleSeeder --force
 */
class SupplierExampleSeeder extends Seeder
{
    public function run(SupplierOfferService $offers): void
    {
        $today = CarbonImmutable::today();

        foreach ($this->suppliers() as $row) {
            // withTrashed: si el equipo ya borro este ejemplo, no vuelve.
            if (Supplier::withTrashed()->where('nit', $row['supplier']['nit'])->exists()) {
                continue;
            }

            $supplier = Supplier::create($row['supplier'] + ['is_example' => true, 'active' => true]);

            $branches = [];
            foreach ($row['branches'] as $i => $branch) {
                $branches[] = $supplier->branches()->create($branch + ['is_main' => $i === 0]);
            }

            foreach ($row['contacts'] as $i => $contact) {
                $branchIndex = $contact['branch'] ?? 0;
                unset($contact['branch']);

                $supplier->contacts()->create($contact + [
                    'supplier_branch_id' => $branches[$branchIndex]->id ?? null,
                    'whatsapp' => $contact['mobile'] ?? null,
                    'is_primary' => $i === 0,
                ]);
            }

            foreach ($row['offers'] as $offer) {
                $item = SupplyItem::firstOrCreate(
                    ['name' => $offer['item']['name'], 'is_example' => true],
                    $offer['item'],
                );

                if (SupplierItem::where('supplier_id', $supplier->id)->where('supply_item_id', $item->id)->exists()) {
                    continue;
                }

                $offers->create($supplier, [
                    'supply_item_id' => $item->id,
                    'supplier_ref' => $offer['ref'] ?? null,
                    'price' => $offer['price'],
                    'currency' => $offer['currency'] ?? 'COP',
                    'lead_time_days' => $offer['lead'] ?? null,
                    // Fechas relativas a hoy: asi siempre hay un precio reciente y
                    // uno desactualizado que enseñe el aviso.
                    'price_date' => $today->subDays($offer['days_ago'] ?? 10)->toDateString(),
                    'source' => $offer['source'] ?? 'cotizacion',
                    'is_preferred' => $offer['preferred'] ?? false,
                    'notes' => $offer['notes'] ?? null,
                ], null, null);
            }
        }
    }

    private function suppliers(): array
    {
        // Items que venden dos proveedores: el mismo producto con dos precios.
        $contactor = [
            'name' => 'Contactor tripolar 220 V 32 A',
            'item_type' => 'repuesto',
            'category' => 'Componentes eléctricos',
            'manufacturer_ref' => 'LC1-D32M7',
            'unit' => 'und',
            'description' => 'Contactor de potencia tripolar, bobina 220 VAC, 32 A.',
        ];
        $cable = [
            'name' => 'Cable de tracción 10 mm 8x19 Seale',
            'item_type' => 'repuesto',
            'category' => 'Cables y poleas',
            'manufacturer_ref' => '8X19S-10',
            'unit' => 'm',
            'description' => 'Cable de acero para tracción, alma de fibra, 10 mm.',
        ];
        $botonera = [
            'name' => 'Pulsador de botonera de cabina con iluminación LED',
            'item_type' => 'repuesto',
            'category' => 'Botoneras y señalización',
            'manufacturer_ref' => 'BT-LED-24',
            'unit' => 'und',
            'description' => 'Pulsador redondo en acero inoxidable, 24 V, aro luminoso.',
        ];

        return [
            [
                'supplier' => [
                    'business_name' => 'Repuestos Verticales del Centro S.A.S.',
                    'trade_name' => 'Reverticales',
                    'nit' => '900.000.001-1',
                    'supplier_type' => 'distribuidor',
                    'categories' => ['Componentes eléctricos', 'Botoneras y señalización', 'Puertas y operadores'],
                    'brands' => ['Mitsubishi', 'Otis', 'Schindler'],
                    'website' => 'https://www.ejemplo.com',
                    'payment_terms' => 'Crédito 30 días',
                    'credit_days' => 30,
                    'default_lead_time_days' => 3,
                    'rating' => 5,
                    'notes' => 'PROVEEDOR DE EJEMPLO. Distribuidor con inventario local; entrega el mismo día en Bogotá si se pide antes de las 10 a. m.',
                ],
                'branches' => [
                    ['name' => 'Principal Bogotá', 'address' => 'Cra. 00 # 00-01, Zona Industrial', 'city' => 'Bogotá', 'department' => 'Cundinamarca', 'phone' => '601 000 0001', 'mobile' => '300 000 0001', 'email' => 'ventas.bogota@ejemplo.com', 'business_hours' => 'L-V 7:30 a. m. - 5:30 p. m., sábados 8 a. m. - 12 m.'],
                    ['name' => 'Bodega Medellín', 'address' => 'Cl. 00 # 00-02, Guayabal', 'city' => 'Medellín', 'department' => 'Antioquia', 'phone' => '604 000 0002', 'mobile' => '300 000 0002', 'email' => 'ventas.medellin@ejemplo.com', 'business_hours' => 'L-V 8 a. m. - 5 p. m.'],
                ],
                'contacts' => [
                    ['name' => 'Laura Ejemplo Rincón', 'position' => 'Asesora comercial', 'mobile' => '300 000 0011', 'phone' => '601 000 0001', 'extension' => '102', 'email' => 'laura@ejemplo.com', 'branch' => 0, 'notes' => 'Responde rápido por WhatsApp.'],
                    ['name' => 'Camilo Ejemplo Torres', 'position' => 'Asesor técnico', 'mobile' => '300 000 0012', 'email' => 'camilo@ejemplo.com', 'branch' => 0, 'notes' => 'Ayuda a identificar referencias por foto.'],
                    ['name' => 'Paola Ejemplo Mejía', 'position' => 'Asesora comercial Medellín', 'mobile' => '300 000 0013', 'email' => 'paola@ejemplo.com', 'branch' => 1],
                ],
                'offers' => [
                    ['item' => $contactor, 'ref' => 'RV-CT-3220', 'price' => 185000, 'lead' => 1, 'days_ago' => 12, 'preferred' => true],
                    ['item' => $botonera, 'ref' => 'RV-BT-024', 'price' => 42000, 'lead' => 2, 'days_ago' => 30, 'source' => 'lista'],
                    ['item' => ['name' => 'Rodamiento de zapata de puerta de cabina', 'item_type' => 'repuesto', 'category' => 'Puertas y operadores', 'manufacturer_ref' => 'ZP-608', 'unit' => 'und'], 'ref' => 'RV-ZP-608', 'price' => 18500, 'lead' => 1, 'days_ago' => 30, 'source' => 'lista'],
                    ['item' => ['name' => 'Fotocelda de barrera para puerta (cortina de luz)', 'item_type' => 'repuesto', 'category' => 'Puertas y operadores', 'manufacturer_ref' => 'CL-917', 'unit' => 'und', 'description' => 'Cortina de luz infrarroja de 2 m, emisor y receptor.'], 'ref' => 'RV-CL-917', 'price' => 690000, 'lead' => 3, 'days_ago' => 140, 'notes' => 'Precio antiguo: sirve para ver el aviso de precio desactualizado.'],
                ],
            ],
            [
                'supplier' => [
                    'business_name' => 'Importadora de Elevación Andina Ltda.',
                    'trade_name' => 'Elevandina',
                    'nit' => '900.000.002-2',
                    'supplier_type' => 'importador',
                    'categories' => ['Tarjetas electrónicas', 'Máquinas y motores', 'Variadores'],
                    'brands' => ['Mitsubishi', 'Fermator', 'Monarch'],
                    'payment_terms' => '50 % anticipo, 50 % contra entrega',
                    'default_currency' => 'USD',
                    'default_lead_time_days' => 25,
                    'rating' => 4,
                    'notes' => 'PROVEEDOR DE EJEMPLO. Importa bajo pedido: cotiza en dólares y el plazo depende del despacho.',
                ],
                'branches' => [
                    ['name' => 'Oficina Cali', 'address' => 'Av. 00 Norte # 00-03, Of. 301', 'city' => 'Cali', 'department' => 'Valle del Cauca', 'phone' => '602 000 0003', 'mobile' => '300 000 0003', 'email' => 'importaciones@ejemplo.com', 'business_hours' => 'L-V 8 a. m. - 5 p. m.'],
                ],
                'contacts' => [
                    ['name' => 'Andrés Ejemplo Valencia', 'position' => 'Gerente de importaciones', 'mobile' => '300 000 0021', 'email' => 'andres@ejemplo.com', 'branch' => 0],
                    ['name' => 'Diana Ejemplo Ospina', 'position' => 'Asistente comercial', 'mobile' => '300 000 0022', 'phone' => '602 000 0003', 'extension' => '12', 'email' => 'diana@ejemplo.com', 'branch' => 0],
                ],
                'offers' => [
                    ['item' => ['name' => 'Tarjeta de control principal para maniobra', 'item_type' => 'repuesto', 'category' => 'Tarjetas electrónicas', 'manufacturer_ref' => 'MCB-3000', 'compatible_brands' => ['Monarch'], 'unit' => 'und', 'description' => 'Tarjeta principal de control, nueva, con garantía de 12 meses.'], 'ref' => 'EA-MCB3000', 'price' => 420, 'currency' => 'USD', 'lead' => 25, 'days_ago' => 20],
                    ['item' => ['name' => 'Variador de frecuencia 7,5 kW para ascensor', 'item_type' => 'repuesto', 'category' => 'Variadores', 'manufacturer_ref' => 'VF-7K5-L', 'unit' => 'und'], 'ref' => 'EA-VF75', 'price' => 980, 'currency' => 'USD', 'lead' => 30, 'days_ago' => 45],
                    ['item' => ['name' => 'Operador de puertas de cabina apertura central 800 mm', 'item_type' => 'repuesto', 'category' => 'Puertas y operadores', 'manufacturer_ref' => 'OP-C800', 'compatible_brands' => ['Fermator'], 'unit' => 'und'], 'ref' => 'EA-OPC800', 'price' => 1250, 'currency' => 'USD', 'lead' => 35, 'days_ago' => 45],
                    ['item' => $contactor, 'ref' => 'EA-LC1D32', 'price' => 168000, 'lead' => 8, 'days_ago' => 60, 'notes' => 'Más barato que el distribuidor local, pero tarda más.'],
                ],
            ],
            [
                'supplier' => [
                    'business_name' => 'Taller Electromecánico El Rotor',
                    'trade_name' => 'El Rotor',
                    'nit' => '900.000.003-3',
                    'supplier_type' => 'taller',
                    'categories' => ['Rebobinado de motores', 'Reparación de tarjetas', 'Mecanizado'],
                    'brands' => [],
                    'payment_terms' => 'Contado',
                    'default_lead_time_days' => 5,
                    'rating' => 4,
                    'notes' => 'PROVEEDOR DE EJEMPLO. Taller de servicios: no vende repuestos, repara. Recoge y entrega en obra.',
                ],
                'branches' => [
                    ['name' => 'Taller', 'address' => 'Cl. 00 Sur # 00-04', 'city' => 'Bogotá', 'department' => 'Cundinamarca', 'phone' => '601 000 0004', 'mobile' => '300 000 0004', 'email' => 'taller@ejemplo.com', 'business_hours' => 'L-S 7 a. m. - 5 p. m.'],
                ],
                'contacts' => [
                    ['name' => 'Hernán Ejemplo Castaño', 'position' => 'Jefe de taller', 'mobile' => '300 000 0031', 'email' => 'hernan@ejemplo.com', 'branch' => 0, 'notes' => 'Para urgencias, llamar directamente.'],
                ],
                'offers' => [
                    ['item' => ['name' => 'Rebobinado de motor de tracción hasta 10 HP', 'item_type' => 'servicio', 'category' => 'Rebobinado de motores', 'unit' => 'servicio', 'description' => 'Rebobinado completo, cambio de rodamientos, barnizado y prueba en banco.'], 'price' => 1850000, 'lead' => 6, 'days_ago' => 15, 'source' => 'telefono'],
                    ['item' => ['name' => 'Reparación de tarjeta electrónica de maniobra', 'item_type' => 'servicio', 'category' => 'Reparación de tarjetas', 'unit' => 'servicio', 'description' => 'Diagnóstico y reparación a nivel de componente. Garantía de 3 meses.'], 'price' => 480000, 'lead' => 4, 'days_ago' => 15, 'source' => 'telefono'],
                    ['item' => ['name' => 'Rectificado de polea de tracción', 'item_type' => 'servicio', 'category' => 'Mecanizado', 'unit' => 'servicio'], 'price' => 920000, 'lead' => 5, 'days_ago' => 100, 'source' => 'telefono'],
                ],
            ],
            [
                'supplier' => [
                    'business_name' => 'Cables y Lubricantes Industriales La Estación S.A.S.',
                    'trade_name' => 'La Estación',
                    'nit' => '900.000.004-4',
                    'supplier_type' => 'distribuidor',
                    'categories' => ['Cables y poleas', 'Lubricantes', 'Ferretería'],
                    'brands' => [],
                    'payment_terms' => 'Crédito 15 días',
                    'credit_days' => 15,
                    'default_lead_time_days' => 2,
                    'rating' => 3,
                    'notes' => 'PROVEEDOR DE EJEMPLO. Insumos de consumo frecuente. Despacha a todo el país.',
                ],
                'branches' => [
                    ['name' => 'Punto de venta Barranquilla', 'address' => 'Vía 00 # 00-05, Bodega 7', 'city' => 'Barranquilla', 'department' => 'Atlántico', 'phone' => '605 000 0005', 'mobile' => '300 000 0005', 'email' => 'pedidos@ejemplo.com', 'business_hours' => 'L-V 7 a. m. - 4:30 p. m.'],
                    ['name' => 'Punto de venta Bogotá', 'address' => 'Cl. 00 # 00-06', 'city' => 'Bogotá', 'department' => 'Cundinamarca', 'phone' => '601 000 0006', 'email' => 'bogota@ejemplo.com'],
                ],
                'contacts' => [
                    ['name' => 'Marcela Ejemplo Duarte', 'position' => 'Ejecutiva de cuenta', 'mobile' => '300 000 0041', 'email' => 'marcela@ejemplo.com', 'branch' => 0],
                    ['name' => 'Jorge Ejemplo Niño', 'position' => 'Mostrador Bogotá', 'mobile' => '300 000 0042', 'email' => 'jorge@ejemplo.com', 'branch' => 1],
                ],
                'offers' => [
                    ['item' => $cable, 'ref' => 'LE-CAB10', 'price' => 14800, 'lead' => 2, 'days_ago' => 8, 'source' => 'lista', 'preferred' => true],
                    ['item' => ['name' => 'Aceite para guías ISO 68', 'item_type' => 'insumo', 'category' => 'Lubricantes', 'unit' => 'gal', 'description' => 'Aceite lubricante para guías de cabina y contrapeso.'], 'ref' => 'LE-ISO68-G', 'price' => 96000, 'lead' => 1, 'days_ago' => 8, 'source' => 'lista'],
                    ['item' => ['name' => 'Grasa de litio multipropósito', 'item_type' => 'insumo', 'category' => 'Lubricantes', 'unit' => 'kg'], 'ref' => 'LE-GLI-1K', 'price' => 28500, 'lead' => 1, 'days_ago' => 8, 'source' => 'lista'],
                    ['item' => $botonera, 'ref' => 'LE-PUL-LED', 'price' => 39500, 'lead' => 4, 'days_ago' => 50],
                ],
            ],
        ];
    }
}
