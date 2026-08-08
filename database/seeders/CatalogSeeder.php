<?php

namespace Database\Seeders;

use App\Models\Catalog;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ── Marcas de equipos ──
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'otis',         'label' => 'Otis'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'mitsubishi',   'label' => 'Mitsubishi'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'hyundai',      'label' => 'Hyundai'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'schindler',    'label' => 'Schindler'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'thyssenkrupp', 'label' => 'ThyssenKrupp'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'kone',         'label' => 'KONE'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'lg',           'label' => 'LG'],
            ['scope' => 'equipment', 'category' => 'brand', 'key' => 'sigma',        'label' => 'Sigma'],

            // ── Condiciones iniciales RSTP (12 ítems del formato Excel) ──
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'fuera_de_servicio',      'label' => 'Fuera de Servicio'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'nivelado',               'label' => 'Nivelado'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'sincronizado_piso',      'label' => 'Sincronizado En El Piso'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'pasado_extremos',        'label' => 'Pasado En Extremos'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'puertas_cerradas',       'label' => 'Puertas Cerradas'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'puertas_descarriladas',  'label' => 'Puertas Descarriladas'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'freno',                  'label' => 'Freno'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'luces_interiores',       'label' => 'Luces Interiores'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'botoneras_llamadas',     'label' => 'Botoneras Llamadas'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'foso_inundado',          'label' => 'Foso Inundado'],
            ['scope' => 'RSTP', 'category' => 'initial_condition', 'key' => 'ruidos_generales',       'label' => 'Ruidos En General'],

            // ── Actividades RSTP: Cuarto de Máquinas ──
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cuarto_maquinas', 'key' => 'acometidas_electricas',  'label' => 'Acometidas Eléctricas'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cuarto_maquinas', 'key' => 'control_maniobras',      'label' => 'Control De Maniobras'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cuarto_maquinas', 'key' => 'maquinaria',             'label' => 'Maquinaria'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cuarto_maquinas', 'key' => 'limitador_velocidad',    'label' => 'Limitador De Velocidad'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cuarto_maquinas', 'key' => 'limpieza_general_cm',    'label' => 'Limpieza Del Area En General'],

            // ── Actividades RSTP: Cabina ──
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cabina', 'key' => 'operador_puertas',      'label' => 'Operador De Puertas'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cabina', 'key' => 'caja_conexiones',       'label' => 'Caja De Conexiones'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cabina', 'key' => 'aceiteras_cabina',      'label' => 'Aceiteras Lubricantes/Guides'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cabina', 'key' => 'corral_techo',          'label' => 'Corral Techo'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'cabina', 'key' => 'limpieza_general_cab',  'label' => 'Limpieza En General'],

            // ── Actividades RSTP: Pozo-Foso ──
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'pozo_foso', 'key' => 'puertas_piso',              'label' => 'Puertas De Piso'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'pozo_foso', 'key' => 'instrumentacion_recorrido', 'label' => 'Instrumentación De Recorrido'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'pozo_foso', 'key' => 'aceiteras_pozo',            'label' => 'Aceiteras Lubricantes/Guides'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'pozo_foso', 'key' => 'rieles_guias',              'label' => 'Rieles Guías/Contrapeso'],
            ['scope' => 'RSTP', 'category' => 'rstp_activity', 'group_key' => 'pozo_foso', 'key' => 'limpieza_general_pf',       'label' => 'Limpieza En General'],

            // ── Condiciones iniciales RSTC/RSTE (20 ítems, 2 columnas) ──
            // Columna izquierda
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'fuera_de_servicio',        'label' => 'Fuera de Servicio'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'nivelado',                 'label' => 'Nivelado'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'sincronizado_piso',        'label' => 'Sincronizado En El Piso'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'puertas_cerradas',         'label' => 'Puertas Cerradas'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'puertas_abiertas',         'label' => 'Puertas Abiertas'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'puertas_descarriladas',    'label' => 'Puertas Descarriladas'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'pasado_extremo_superior',  'label' => 'Pasado Extremo Superior'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'pasado_extremo_inferior',  'label' => 'Pasado Extremo Inferior'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'freno_mecanico',           'label' => 'Freno Mecánico'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'left', 'key' => 'control_principal',        'label' => 'Control Principal'],
            // Columna derecha
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'variador_principal',      'label' => 'Variador Principal'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'acometida_electrica',     'label' => 'Acometida Eléctrica'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'cable_viajero',           'label' => 'Cable Viajero'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'cable_pozo',              'label' => 'Cable De Pozo'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'limitador_velocidad',     'label' => 'Limitador De Velocidad'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'luces_interiores',        'label' => 'Luces Interiores'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'ventilacion',             'label' => 'Ventilación'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'fotocelula',              'label' => 'Fotocélula/Cortina Infrarroja'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'foso_inundado',           'label' => 'Foso Inundado'],
            ['scope' => 'RSTC', 'category' => 'initial_condition', 'group_key' => 'right', 'key' => 'ruidos_generales',        'label' => 'Ruidos En General'],

            // ── Trabajos RSTE: Cuarto de Máquinas ──
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cuarto_maquinas', 'key' => 'acometidas_electricas',  'label' => 'Acometidas Eléctricas'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cuarto_maquinas', 'key' => 'control_maniobras',      'label' => 'Control De Maniobras'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cuarto_maquinas', 'key' => 'maquina_traccion',       'label' => 'Máquina De Tracción'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cuarto_maquinas', 'key' => 'limitador_velocidad',    'label' => 'Limitador De Velocidad'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cuarto_maquinas', 'key' => 'alambrado_pozo_foso',    'label' => 'Alambrado De Pozo-Foso'],

            // ── Trabajos RSTE: Cabina ──
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cabina', 'key' => 'operador_puertas',        'label' => 'Operador De Puertas'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cabina', 'key' => 'caja_conexiones',         'label' => 'Caja De Conexiones'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cabina', 'key' => 'aceiteras_cabina',        'label' => 'Aceiteras Lubricantes/Guides'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cabina', 'key' => 'corral_techo',            'label' => 'Corral Techo'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cabina', 'key' => 'microswitches_seguridad', 'label' => 'Microswitches de Seguridad'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'cabina', 'key' => 'fotocelula_cortina',      'label' => 'Fotocélula/Cortina Infrarroja'],

            // ── Trabajos RSTE: Pozo-Foso ──
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'pozo_foso', 'key' => 'puertas_piso',              'label' => 'Puertas De Piso'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'pozo_foso', 'key' => 'instrumentacion_recorrido', 'label' => 'Instrumentación De Recorrido'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'pozo_foso', 'key' => 'aceiteras_pozo',            'label' => 'Aceiteras Lubricantes/Guides'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'pozo_foso', 'key' => 'microswitches_seguridad_pf','label' => 'Microswitches de Seguridad'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'pozo_foso', 'key' => 'rieles_guias',              'label' => 'Rieles Guías/Contrapeso'],
            ['scope' => 'RSTE', 'category' => 'rste_work', 'group_key' => 'pozo_foso', 'key' => 'amortiguadores',            'label' => 'Amortiguadores'],
        ];

        $order = 0;
        foreach ($items as $item) {
            Catalog::create(array_merge($item, ['sort_order' => $order++]));
        }
    }
}
