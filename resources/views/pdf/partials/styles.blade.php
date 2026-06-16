{{-- Estilos compartidos de los reportes PDF (RSTP/RSTC/RSTE).
     Optimizados para que el reporte quepa en una sola hoja:
     sin padding de body (Browsershot ya aplica márgenes), celdas y secciones
     compactas. --}}
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size:9px; color:#000; }
    table { width:100%; border-collapse:collapse; }
    .section-title { background:#30ab0a; color:#fff; font-size:9px; font-weight:bold; padding:3px 6px; margin-top:6px; }
    .grid td, .grid th { border:1px solid #999; padding:2px 5px; font-size:8px; }
    .grid th { background:#e8f5e4; font-weight:bold; text-align:left; }
    .check { width:12px; height:12px; border:1px solid #333; display:inline-block; text-align:center; font-size:8px; line-height:12px; }
    .check.marked { background:#30ab0a; color:#fff; }
    .sig-box { border:1px solid #999; min-height:50px; padding:4px; }
    .sig-img { max-height:48px; max-width:180px; }
</style>
