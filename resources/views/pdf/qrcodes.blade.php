<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Códigos QR de Equipos</title>
    <style>
        @page {
            size: Letter;
            margin: 1cm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
        }

        .sheet-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .sheet-subtitle {
            text-align: center;
            font-size: 9px;
            color: #64748b;
            margin-bottom: 14px;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
        }

        /* 2 columnas x 3 filas = 6 códigos por hoja carta */
        .card {
            width: 50%;
            height: 8.1cm;
            padding: 0.35cm;
            page-break-inside: avoid;
        }

        .card-inner {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.3cm;
            text-align: center;
        }

        .card-client {
            font-size: 10px;
            color: #475569;
            margin-bottom: 4px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-qr {
            width: 4.4cm;
            height: 4.4cm;
        }

        .card-code {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 6px;
        }

        .card-site {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 2px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .empty {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="sheet-title">Códigos QR de Equipos — Ascensores Tzion</div>
    <div class="sheet-subtitle">{{ $items->count() }} equipos · Generado el {{ now()->format('d/m/Y H:i') }}</div>

    @if ($items->isEmpty())
        <div class="empty">No hay equipos para mostrar.</div>
    @else
        <div class="grid">
            @foreach ($items as $item)
                <div class="card">
                    <div class="card-inner">
                        <div class="card-client">{{ $item['client'] }}</div>
                        <img class="card-qr" src="{{ $item['qr'] }}" alt="QR {{ $item['code'] }}">
                        <div class="card-code">{{ $item['code'] }}</div>
                        @if (!empty($item['site']))
                            <div class="card-site">{{ $item['site'] }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
