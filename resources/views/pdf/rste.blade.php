<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size:9px; color:#000; padding:15px; }
        table { width:100%; border-collapse:collapse; }
        .section-title { background:#30ab0a; color:#fff; font-size:9px; font-weight:bold; padding:4px 6px; margin-top:10px; }
        .grid td, .grid th { border:1px solid #999; padding:3px 5px; font-size:8px; }
        .grid th { background:#e8f5e4; font-weight:bold; text-align:left; }
        .check { width:12px; height:12px; border:1px solid #333; display:inline-block; text-align:center; font-size:8px; line-height:12px; }
        .check.marked { background:#30ab0a; color:#fff; }
        .sig-box { border:1px solid #999; min-height:60px; padding:4px; }
        .sig-img { max-height:55px; max-width:180px; }
    </style>
</head>
<body>

@include('pdf.partials.header', ['title' => 'REPORTE DE SERVICIO TÉCNICO ESPECIAL (RSTE)'])

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
        <td>{{ \App\Models\Catalog::where('key', $left->condition_key)->where('scope', 'RSTE')->value('label') ?? $left->condition_key }}</td>
        <td style="text-align:center;">{!! $left->value === 'si' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! $left->value === 'no' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        @else
        <td></td><td></td><td></td>
        @endif
        <td style="border-left:2px solid #999; border-right:2px solid #999;"></td>
        @php $right = $rightConditions->values()->get($i); @endphp
        @if($right)
        <td>{{ \App\Models\Catalog::where('key', $right->condition_key)->where('scope', 'RSTE')->value('label') ?? $right->condition_key }}</td>
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

{{-- TRABAJOS Y/O REPARACIONES A EFECTUAR --}}
<div class="section-title" style="margin-top:10px;">TRABAJOS Y/O REPARACIONES A EFECTUAR</div>
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:20%;">Grupo</th>
        <th style="width:30%;">Descripción</th>
        <th style="width:8%; text-align:center;">Ok</th>
        <th style="width:42%;">Observaciones</th>
    </tr>
    @php
        $groups = ['cuarto_maquinas' => 'Cuarto de Máquinas', 'cabina' => 'Cabina', 'pozo_foso' => 'Pozo-Foso'];
        $works = $report->rsteWorks->groupBy('group_key');
    @endphp
    @foreach($groups as $gk => $gLabel)
        @php $items = $works->get($gk, collect()); @endphp
        @foreach($items as $idx => $work)
        <tr>
            @if($idx === 0)
            <td rowspan="{{ $items->count() }}" style="vertical-align:top; font-weight:bold;">{{ $gLabel }}</td>
            @endif
            <td>{{ \App\Models\Catalog::where('key', $work->work_key)->where('scope', 'RSTE')->value('label') ?? $work->work_key }}</td>
            <td style="text-align:center;">{!! $work->is_ok ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
            <td>{{ $work->observation ?? '' }}</td>
        </tr>
        @endforeach
    @endforeach
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
        <td>¿Genera Alguna Otra Cotización?</td>
        <td style="text-align:center;">{!! $report->generates_quotation ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! !$report->generates_quotation ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
    </tr>
    <tr>
        <td>¿Requiere Algún Otro Cambio De Repuestos?</td>
        <td style="text-align:center;">{!! $report->requires_parts_change ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! !$report->requires_parts_change ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
    </tr>
</table>

{{-- FIRMAS Y TIEMPOS --}}
<table style="width:100%; margin-top:12px; border-collapse:collapse;">
    <tr>
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
            <strong style="font-size:8px;">FIRMA Y CC CLIENTE</strong><br>
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
            <strong style="font-size:8px;">TIEMPO DE EJECUCIÓN</strong><br>
            <table style="width:100%; font-size:8px; margin-top:4px;">
                <tr><td>Hora Entrada:</td><td><strong>{{ $report->time_in ?? '—' }}</strong></td></tr>
                <tr><td>Hora Salida:</td><td><strong>{{ $report->time_out ?? '—' }}</strong></td></tr>
            </table>
        </td>
    </tr>
</table>

@include('pdf.partials.footer')

</body>
</html>
