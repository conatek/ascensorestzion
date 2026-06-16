<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>

@include('pdf.partials.header', ['title' => 'REPORTE DE SERVICIO TÉCNICO CORRECTIVO (RSTC)'])

{{-- INFORMACIÓN BÁSICA --}}
<div class="section-title">INFORMACIÓN BÁSICA</div>
<table class="grid" style="margin-top:2px;">
    <tr>
        <td style="width:15%;"><strong>Cliente:</strong></td>
        <td style="width:35%;">{{ $report->client->business_name ?? '' }}</td>
        <td style="width:15%;"><strong>Ped. Cliente:</strong></td>
        <td style="width:35%;">{{ $report->customer_order_ref ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Obra:</strong></td>
        <td>{{ $report->site->name ?? '' }}</td>
        <td><strong>Equipo:</strong></td>
        <td>{{ $report->equipment->internal_code ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Marca:</strong></td>
        <td>{{ $report->equipment->brand ?? '' }}</td>
        <td><strong>Modelo:</strong></td>
        <td>{{ $report->equipment->model ?? '' }}</td>
    </tr>
</table>

{{-- CONDICIÓN INICIAL --}}
<div class="section-title" style="margin-top:10px;">INFORMACIÓN DE CONDICIÓN INICIAL DEL EQUIPO</div>
@php
    $conditionsByGroup = $report->initialConditions->groupBy('group_key');
    $leftConditions = $conditionsByGroup->get('left', collect());
    $rightConditions = $conditionsByGroup->get('right', collect());
    $maxRows = max($leftConditions->count(), $rightConditions->count());
@endphp
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:30%;">Descripción</th>
        <th style="width:8%; text-align:center;">Sí</th>
        <th style="width:8%; text-align:center;">No</th>
        <th style="width:4%;"></th>
        <th style="width:30%;">Descripción</th>
        <th style="width:8%; text-align:center;">Sí</th>
        <th style="width:8%; text-align:center;">No</th>
    </tr>
    @for($i = 0; $i < $maxRows; $i++)
    <tr>
        @php $left = $leftConditions->values()->get($i); @endphp
        @if($left)
        <td>{{ \App\Models\Catalog::where('key', $left->condition_key)->where('scope', 'RSTC')->value('label') ?? $left->condition_key }}</td>
        <td style="text-align:center;">{!! $left->value === 'si' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! $left->value === 'no' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        @else
        <td></td><td></td><td></td>
        @endif
        <td style="border-left:2px solid #999; border-right:2px solid #999;"></td>
        @php $right = $rightConditions->values()->get($i); @endphp
        @if($right)
        <td>{{ \App\Models\Catalog::where('key', $right->condition_key)->where('scope', 'RSTC')->value('label') ?? $right->condition_key }}</td>
        <td style="text-align:center;">{!! $right->value === 'si' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! $right->value === 'no' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        @else
        <td></td><td></td><td></td>
        @endif
    </tr>
    @endfor
</table>

{{-- CÓDIGOS DE FALLA --}}
<div class="section-title" style="margin-top:10px;">CÓDIGOS DE FALLA</div>
@php
    $faultCodes = $report->faultCodes;
    $leftCodes = $faultCodes->filter(fn($fc) => (int) substr($fc->code, 1) <= 10)->values();
    $rightCodes = $faultCodes->filter(fn($fc) => (int) substr($fc->code, 1) > 10)->values();
    $maxFaultRows = max($leftCodes->count(), $rightCodes->count(), 1);
@endphp
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:10%; text-align:center;">Código</th>
        <th style="width:38%;">Descripción</th>
        <th style="width:4%;"></th>
        <th style="width:10%; text-align:center;">Código</th>
        <th style="width:38%;">Descripción</th>
    </tr>
    @for($i = 0; $i < $maxFaultRows; $i++)
    <tr>
        @php $lc = $leftCodes->get($i); @endphp
        <td style="text-align:center;">{{ $lc->code ?? '' }}</td>
        <td>{{ $lc->description ?? '' }}</td>
        <td style="border-left:2px solid #999; border-right:2px solid #999;"></td>
        @php $rc = $rightCodes->get($i); @endphp
        <td style="text-align:center;">{{ $rc->code ?? '' }}</td>
        <td>{{ $rc->description ?? '' }}</td>
    </tr>
    @endfor
</table>

{{-- REVISIÓN, DIAGNÓSTICO Y SOLUCIÓN --}}
<div class="section-title" style="margin-top:10px;">REVISIÓN, DIAGNÓSTICO Y SOLUCIÓN</div>
@php
    $details = $report->rstcDetails;
    $faultLocationOptions = [
        'sistema_de_puertas' => 'Sistema De Puertas',
        'control_de_maniobras' => 'Control De Maniobras',
        'maquinaria' => 'Maquinaria',
        'recorrido' => 'Recorrido',
        'otra' => 'Otra',
    ];
    $faultCauseOptions = [
        'energia_externa' => 'Energía Externa',
        'inundacion_humedad' => 'Inundación/Humedad',
        'provocada_por_tercero' => 'Provocada Por Tercero',
        'tecnica_por_el_equipo' => 'Técnica Por El Equipo',
        'otra' => 'Otra',
    ];
    $faultSolutionOptions = [
        'control_de_maniobras' => 'Control De Maniobras',
        'perifericos' => 'Periféricos',
        'puertas_de_piso' => 'Puertas De Piso',
        'operador_puertas_de_cabina' => 'Operador/Puertas De Cabina',
        'activacion_interruptores' => 'Activación Interruptores',
        'otra' => 'Otra',
    ];
@endphp
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:25%;">Categoría</th>
        <th>Opciones</th>
    </tr>
    <tr>
        <td><strong>Ubicación de La Falla</strong></td>
        <td>
            @foreach($faultLocationOptions as $key => $label)
                <span class="check {{ ($details->fault_location ?? '') === $key ? 'marked' : '' }}">{{ ($details->fault_location ?? '') === $key ? '✓' : '' }}</span>
                {{ $label }}&nbsp;&nbsp;
            @endforeach
        </td>
    </tr>
    <tr>
        <td><strong>Causas de La Falla</strong></td>
        <td>
            @foreach($faultCauseOptions as $key => $label)
                <span class="check {{ ($details->fault_cause ?? '') === $key ? 'marked' : '' }}">{{ ($details->fault_cause ?? '') === $key ? '✓' : '' }}</span>
                {{ $label }}&nbsp;&nbsp;
            @endforeach
        </td>
    </tr>
    <tr>
        <td><strong>Solución de La Falla</strong></td>
        <td>
            @foreach($faultSolutionOptions as $key => $label)
                <span class="check {{ ($details->fault_solution ?? '') === $key ? 'marked' : '' }}">{{ ($details->fault_solution ?? '') === $key ? '✓' : '' }}</span>
                {{ $label }}&nbsp;&nbsp;
            @endforeach
        </td>
    </tr>
    @if($details->notes ?? null)
    <tr>
        <td><strong>Notas</strong></td>
        <td>{{ $details->notes }}</td>
    </tr>
    @endif
</table>

{{-- CONCLUSIÓN --}}
<div class="section-title" style="margin-top:10px;">CONCLUSIÓN DEL SERVICIO TÉCNICO</div>
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:50%;">Pregunta</th>
        <th style="width:8%; text-align:center;">Sí</th>
        <th style="width:8%; text-align:center;">No</th>
        <th style="width:34%;">Observaciones</th>
    </tr>
    <tr>
        <td>¿El Equipo Queda Funcionando?</td>
        <td style="text-align:center;">{!! $report->equipment_functional ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! $report->equipment_functional === false ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td rowspan="3" style="vertical-align:top;">{{ $report->conclusion_notes ?? '' }}</td>
    </tr>
    <tr>
        <td>¿Genera Alguna Cotización?</td>
        <td style="text-align:center;">{!! $report->generates_quotation ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! !$report->generates_quotation ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
    </tr>
    <tr>
        <td>¿Requiere Algún Cambio De Repuestos?</td>
        <td style="text-align:center;">{!! $report->requires_parts_change ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! !$report->requires_parts_change ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
    </tr>
</table>

{{-- FIRMAS Y TIEMPOS --}}
<table style="width:100%; margin-top:12px; border-collapse:collapse;">
    <tr>
        <td style="width:33%; vertical-align:top; border:1px solid #999; padding:5px;">
            <strong style="font-size:8px;">FIRMA Y CC DE QUIEN RECIBE</strong><br>
            <div class="sig-box">
                @if($report->customer_signature_path)
                    @php $sigPath = storage_path('app/' . $report->customer_signature_path); @endphp
                    @if(file_exists($sigPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($sigPath)) }}" class="sig-img">
                    @endif
                @endif
            </div>
            <span style="font-size:7.5px;">{{ $report->customer_signer_name ?? '' }} {{ $report->customer_signer_document ? '— CC ' . $report->customer_signer_document : '' }}</span>
        </td>
        <td style="width:33%; vertical-align:top; border:1px solid #999; padding:5px;">
            <strong style="font-size:8px;">FIRMA Y CC DEL TÉCNICO</strong><br>
            <div class="sig-box">
                @if($report->technician_signature_path)
                    @php $sigPath = storage_path('app/' . $report->technician_signature_path); @endphp
                    @if(file_exists($sigPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($sigPath)) }}" class="sig-img">
                    @endif
                @endif
            </div>
            <span style="font-size:7.5px;">{{ $report->technician->name ?? '' }}</span>
        </td>
        <td style="width:33%; vertical-align:top; border:1px solid #999; padding:5px;">
            <strong style="font-size:8px;">TIEMPO DE ATENCIÓN / EJECUCIÓN</strong><br>
            <table style="width:100%; font-size:8px; margin-top:4px;">
                <tr><td>Hora Llamada:</td><td><strong>{{ $details->call_time ?? '—' }}</strong></td></tr>
                <tr><td>Hora Entrada:</td><td><strong>{{ $details->entry_time ?? '—' }}</strong></td></tr>
                <tr><td>Hora Salida:</td><td><strong>{{ $details->exit_time ?? '—' }}</strong></td></tr>
                <tr>
                    <td>Tiempo Resp:</td>
                    <td>
                        <strong>
                            @if(($details->response_time_hh ?? null) !== null)
                                {{ str_pad($details->response_time_hh, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($details->response_time_mm ?? 0, 2, '0', STR_PAD_LEFT) }}
                            @else
                                —
                            @endif
                        </strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@include('pdf.partials.footer')

@include('pdf.partials.anexos')

</body>
</html>
