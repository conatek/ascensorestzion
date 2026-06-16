<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>

@include('pdf.partials.header', ['title' => 'REPORTE DE SERVICIO TÉCNICO PREVENTIVO (RSTP)'])

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
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:30%;">Descripción</th>
        <th style="width:8%; text-align:center;">Sí</th>
        <th style="width:8%; text-align:center;">No</th>
        <th style="width:54%;">Observación</th>
    </tr>
    @foreach($report->initialConditions as $cond)
    <tr>
        <td>{{ \App\Models\Catalog::where('key', $cond->condition_key)->where('scope', 'RSTP')->value('label') ?? $cond->condition_key }}</td>
        <td style="text-align:center;">{!! $cond->value === 'si' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td style="text-align:center;">{!! $cond->value === 'no' ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
        <td>{{ $cond->observation ?? '' }}</td>
    </tr>
    @endforeach
</table>

{{-- MES DEL MANTENIMIENTO --}}
@if($report->rstpMonth)
<table class="grid" style="margin-top:6px;">
    <tr>
        <th colspan="6" style="text-align:center;">Mantenimiento Preventivo — Mes</th>
    </tr>
    <tr>
        @php $months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']; @endphp
        @foreach($months as $i => $m)
        <td style="text-align:center; width:8.33%; {{ ($report->rstpMonth->month === $i+1) ? 'background:#30ab0a;color:#fff;font-weight:bold;' : '' }}">
            {{ $m }}
        </td>
        @endforeach
    </tr>
</table>
@endif

{{-- ACTIVIDADES RELEVANTES --}}
<div class="section-title" style="margin-top:10px;">INFORMACIÓN DE ACTIVIDADES RELEVANTES</div>
<table class="grid" style="margin-top:2px;">
    <tr>
        <th style="width:20%;">Grupo</th>
        <th style="width:30%;">Descripción</th>
        <th style="width:8%; text-align:center;">Ok</th>
        <th style="width:42%;">Observaciones</th>
    </tr>
    @php
        $groups = ['cuarto_maquinas' => 'Cuarto de Máquinas', 'cabina' => 'Cabina', 'pozo_foso' => 'Pozo-Foso'];
        $activities = $report->rstpActivities->groupBy('group_key');
    @endphp
    @foreach($groups as $gk => $gLabel)
        @php $items = $activities->get($gk, collect()); @endphp
        @foreach($items as $idx => $act)
        <tr>
            @if($idx === 0)
            <td rowspan="{{ $items->count() }}" style="vertical-align:top; font-weight:bold;">{{ $gLabel }}</td>
            @endif
            <td>{{ \App\Models\Catalog::where('key', $act->activity_key)->where('scope', 'RSTP')->value('label') ?? $act->activity_key }}</td>
            <td style="text-align:center;">{!! $act->is_ok ? '<span class="check marked">✓</span>' : '<span class="check"></span>' !!}</td>
            <td>{{ $act->observation ?? '' }}</td>
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

@include('pdf.partials.anexos')

</body>
</html>
