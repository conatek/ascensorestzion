<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $client;

    public function __construct()
    {
        $this->client = new Cloudinary(config('cloudinary.cloud_url'));
    }

    /**
     * Devuelve la carpeta raíz según el entorno ('local' o 'production').
     */
    public static function envFolder(): string
    {
        return app()->environment('production') ? 'production' : 'local';
    }

    /**
     * Construye la ruta de carpeta para un recurso de una empresa.
     * Ejemplo: local/mi-empresa/logo
     *
     * Categorías: logo | personas | productos | servicios | iconos | videos
     */
    public static function companyFolder(string $slug, string $category): string
    {
        return self::envFolder().'/'.$slug.'/'.$category;
    }

    /**
     * Sube un archivo a Cloudinary y retorna la URL pública y el public_id.
     * Para video incluye además resource_type ('video') y duration (segundos).
     *
     * @return array{url: string, public_id: string, resource_type: string, duration: float|null}
     */
    public function upload(UploadedFile $file, string $folder = 'general'): array
    {
        $result = $this->client->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'auto',
        ]);

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'resource_type' => $result['resource_type'] ?? 'image',
            'duration' => isset($result['duration']) ? (float) $result['duration'] : null,
        ];
    }

    /**
     * Elimina un recurso de video (resource_type video requiere especificarlo).
     */
    public function destroyVideo(string $publicId): void
    {
        $this->client->uploadApi()->destroy($publicId, ['resource_type' => 'video']);
    }

    /**
     * Elimina un recurso de Cloudinary por su public_id.
     */
    public function destroy(string $publicId): void
    {
        $this->client->uploadApi()->destroy($publicId);
    }
}
