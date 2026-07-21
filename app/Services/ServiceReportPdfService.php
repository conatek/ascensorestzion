<?php

namespace App\Services;

use App\Models\ServiceReport;
use Spatie\Browsershot\Browsershot;

/**
 * Renderiza el PDF de un reporte de servicio. Lo usan la descarga autenticada,
 * el envio manual por correo y la descarga publica por reception_token.
 */
class ServiceReportPdfService
{
    private const RELATIONS = [
        'equipment.site.client',
        'technician',
        'initialConditions',
        'rstpActivities',
        'rstpMonth',
        'rstcDetails',
        'faultCodes',
        'rsteWorks',
        'attachments',
    ];

    public function render(ServiceReport $report): string
    {
        $report->load(self::RELATIONS);

        $view = 'pdf.'.strtolower($report->report_type);
        $html = view($view, ['report' => $report])->render();

        return $this->fromHtml($html);
    }

    public function fromHtml(string $html): string
    {
        $browsershot = Browsershot::html($html)
            ->format('Letter')
            ->margins(8, 8, 8, 8)
            ->showBackground()
            ->waitUntilNetworkIdle() // esperar imágenes remotas (anexo Cloudinary)
            ->noSandbox();

        $chromePath = env('BROWSERSHOT_CHROME_PATH');
        $nodePath = env('BROWSERSHOT_NODE_PATH');

        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }
        if ($nodePath) {
            $browsershot->setNodeBinary($nodePath);
        }

        return $browsershot->pdf();
    }
}
