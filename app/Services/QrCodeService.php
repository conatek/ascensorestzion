<?php

namespace App\Services;

use App\Models\Equipment;
use chillerlan\QRCode\{QRCode, QROptions};
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generate(Equipment $equipment): string
    {
        $content = $this->getContent($equipment);

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 10,
            'imageBase64' => false,
            'eccLevel' => QRCode::ECC_H,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);

        $qrImage = (new QRCode($options))->render($content);

        $directory = 'qrcodes';
        $filename = "equipment-{$equipment->internal_code}.png";
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $qrImage);

        $equipment->update(['qr_code_path' => $path]);

        return $path;
    }

    public function getContent(Equipment $equipment): string
    {
        $baseUrl = config('app.url');

        return "{$baseUrl}/tech/checkin?code={$equipment->internal_code}";
    }

    public function getPublicUrl(Equipment $equipment): ?string
    {
        if (! $equipment->qr_code_path) {
            return null;
        }

        return Storage::disk('public')->url($equipment->qr_code_path);
    }
}
