<?php

namespace App\Services;

use App\Models\Equipment;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    private function options(bool $base64): QROptions
    {
        return new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => $base64,
            'scale' => 10,
            'eccLevel' => EccLevel::H,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);
    }

    public function generate(Equipment $equipment): string
    {
        $content = $this->getContent($equipment);

        $qrImage = (new QRCode($this->options(false)))->render($content);

        $directory = 'qrcodes';
        $filename = "equipment-{$equipment->internal_code}.png";
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $qrImage);

        $equipment->update(['qr_code_path' => $path]);

        return $path;
    }

    public function getBase64(Equipment $equipment): string
    {
        return (new QRCode($this->options(true)))->render($this->getContent($equipment));
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
