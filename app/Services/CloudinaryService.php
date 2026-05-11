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
        return self::envFolder() . '/' . $slug . '/' . $category;
    }

    /**
     * Sube un archivo a Cloudinary y retorna la URL pública y el public_id.
     *
     * @return array{url: string, public_id: string}
     */
    public function upload(UploadedFile $file, string $folder = 'general'): array
    {
        $result = $this->client->uploadApi()->upload($file->getRealPath(), [
            'folder'        => $folder,
            'resource_type' => 'auto',
        ]);

        return [
            'url'       => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    /**
     * Elimina un recurso de Cloudinary por su public_id.
     */
    public function destroy(string $publicId): void
    {
        $this->client->uploadApi()->destroy($publicId);
    }
}
