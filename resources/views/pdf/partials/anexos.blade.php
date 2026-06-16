{{-- Anexo de evidencias del reporte: fotos agrupadas por punto en hoja(s)
     independiente(s) de la principal. El video se referencia (no se incrusta). --}}
@php
    $attachments = $report->attachments ?? collect();
    $annexPhotos = $attachments->where('media_type', '!=', 'video');
    $annexVideos = $attachments->where('media_type', 'video');

    $groupLabels = ['cuarto_maquinas' => 'Cuarto de Máquinas', 'cabina' => 'Cabina', 'pozo_foso' => 'Pozo-Foso'];

    $pointLabel = function ($a) use ($report, $groupLabels) {
        if ($a->condition_key) {
            return \App\Models\Catalog::where('key', $a->condition_key)->where('scope', $report->report_type)->value('label')
                ?? ucwords(str_replace('_', ' ', $a->condition_key));
        }
        if ($a->activity_key) {
            $act = \App\Models\Catalog::where('key', $a->activity_key)->where('scope', $report->report_type)->value('label')
                ?? ucwords(str_replace('_', ' ', $a->activity_key));
            $grp = $groupLabels[$a->group_key] ?? '';
            return $grp ? "{$grp} — {$act}" : $act;
        }
        return 'General';
    };

    $groupedPhotos = $annexPhotos->groupBy($pointLabel);
@endphp

@if($annexPhotos->count() || $annexVideos->count())
<div style="page-break-before: always;">
    @include('pdf.partials.header', ['title' => 'ANEXO — REGISTRO FOTOGRÁFICO Y VIDEO'])

    @foreach($groupedPhotos as $label => $items)
        <div class="section-title" style="margin-top:6px;">{{ $label }}</div>
        <div style="margin-top:4px;">
            @foreach($items as $a)
                <div style="display:inline-block; width:32%; margin:0 0.6% 6px 0; vertical-align:top; text-align:center;">
                    <img src="{{ str_replace('/upload/', '/upload/w_500,h_500,c_limit,q_auto/', $a->url) }}"
                         style="width:100%; border:1px solid #999; border-radius:4px;">
                </div>
            @endforeach
        </div>
    @endforeach

    @if($annexVideos->count())
        <div class="section-title" style="margin-top:8px;">VIDEO DEL SERVICIO</div>
        <p style="font-size:8px; margin-top:4px; color:#475569;">
            <em>Se registró {{ $annexVideos->count() }} video{{ $annexVideos->count() > 1 ? 's' : '' }}
            durante el servicio, disponible{{ $annexVideos->count() > 1 ? 's' : '' }} para visualización en la aplicación.</em>
        </p>
    @endif

    @include('pdf.partials.footer')
</div>
@endif
