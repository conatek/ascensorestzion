<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .header { background: #30ab0a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .detail { margin: 8px 0; }
        .label { font-weight: bold; color: #606060; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">Ascensores Tzion</h2>
        <p style="margin:5px 0 0;">Reporte de Servicio Técnico</p>
    </div>

    <div class="content">
        <p>Se adjunta el reporte de servicio técnico con los siguientes datos:</p>

        <div class="detail"><span class="label">N° Reporte:</span> {{ $report->report_number }}</div>
        <div class="detail"><span class="label">Tipo:</span> {{ $report->report_type }}</div>
        <div class="detail"><span class="label">Fecha:</span> {{ $report->service_date->format('d/m/Y') }}</div>
        <div class="detail"><span class="label">Equipo:</span> {{ $report->equipment->internal_code ?? '' }}</div>
        <div class="detail"><span class="label">Cliente:</span> {{ $report->client->business_name ?? '' }}</div>
        <div class="detail"><span class="label">Sede:</span> {{ $report->site->name ?? '' }}</div>
        <div class="detail"><span class="label">Estado:</span> {{ $report->status }}</div>

        <p style="margin-top: 20px;">El PDF completo se encuentra adjunto a este correo.</p>
    </div>

    <div class="footer">
        Ascensores y Nivelaciones Eléctricas ATZION S.A.S.<br>
        Carrera 78 # 41-32, Laureles Lorena, Medellín - Antioquia<br>
        PBX: +57 (604) 322 5315 | operaciones@ascensorestzion.com
    </div>
</body>
</html>
