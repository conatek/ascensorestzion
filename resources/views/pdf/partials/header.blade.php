<table style="width:100%; border-collapse:collapse; margin-bottom:6px;">
    <tr>
        <td style="width:25%; vertical-align:middle;">
            <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('images/logo/logo-atzion.svg'))) }}" alt="Ascensores Tzion" style="height:26px; width:auto;"><br>
            <span style="font-size:8px; color:#606060;">NIT: 901.334.693-6</span>
        </td>
        <td style="width:50%; text-align:center; vertical-align:middle;">
            <strong style="font-size:13px;">{{ $title }}</strong>
        </td>
        <td style="width:25%; text-align:right; vertical-align:middle;">
            <span style="font-size:9px;">N° <strong>{{ $report->report_number }}</strong></span><br>
            <span style="font-size:9px;">Fecha: <strong>{{ $report->service_date->format('d/m/Y') }}</strong></span>
        </td>
    </tr>
</table>
