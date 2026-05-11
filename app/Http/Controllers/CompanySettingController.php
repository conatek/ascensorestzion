<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Traits\HasCompanyAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CloudinaryService;

class CompanySettingController extends Controller
{
    use HasCompanyAccess;

    /**
     * Obtener lista de plantillas disponibles
     */
    public function templates(): JsonResponse
    {
        $templates = config('templates.available', []);

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Obtener el schema de una plantilla específica
     */
    public function schema(string $templateName): JsonResponse
    {
        $schema = config("templates.schemas.{$templateName}");

        if (!$schema) {
            return response()->json([
                'message' => 'Plantilla no encontrada',
            ], 404);
        }

        return response()->json([
            'template' => $templateName,
            'schema' => $schema,
        ]);
    }

    /**
     * Obtener la configuración de una empresa
     */
    public function show(Request $request, Company $company): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_settings'), 403, 'No autorizado.');
        $this->enforceCompanyAccess($user, $company);

        $settings = $company->getOrCreateSettings();
        $schema = config("templates.schemas.{$settings->template_name}", []);

        return response()->json([
            'settings' => $settings,
            'schema' => $schema,
            'full_customization' => $settings->full_customization,
        ]);
    }

    /**
     * Actualizar la configuración de una empresa
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('edit_settings'), 403, 'No autorizado.');
        $this->enforceCompanyAccess($user, $company);

        $validated = $request->validate([
            'template_name' => 'sometimes|string|in:' . implode(',', array_keys(config('templates.available', []))),
            'customization' => 'sometimes|array',
        ]);

        $settings = $company->getOrCreateSettings();

        // Si cambia la plantilla, cargar valores por defecto de la nueva plantilla
        if (isset($validated['template_name']) && $validated['template_name'] !== $settings->template_name) {
            $newDefaults = config("templates.schemas.{$validated['template_name']}", []);
            $settings->template_name = $validated['template_name'];
            $settings->customization = $this->extractValues($newDefaults);
        }

        // Actualizar customization si se proporciona
        if (isset($validated['customization'])) {
            $settings->customization = array_replace_recursive(
                $settings->customization ?? [],
                $validated['customization']
            );
        }

        $settings->save();

        return response()->json([
            'message' => 'Configuración actualizada correctamente',
            'settings' => $settings->fresh(),
            'full_customization' => $settings->full_customization,
        ]);
    }

    /**
     * Restablecer la configuración a los valores por defecto
     */
    public function reset(Request $request, Company $company): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('edit_settings'), 403, 'No autorizado.');
        $this->enforceCompanyAccess($user, $company);

        $settings = $company->getOrCreateSettings();
        $defaults = config("templates.schemas.{$settings->template_name}", []);

        $settings->customization = $this->extractValues($defaults);
        $settings->save();

        return response()->json([
            'message' => 'Configuración restablecida a valores por defecto',
            'settings' => $settings->fresh(),
            'full_customization' => $settings->full_customization,
        ]);
    }

    /**
     * Subir una imagen para la configuración de plantilla
     */
    public function uploadImage(Request $request, Company $company, CloudinaryService $cloudinary): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('edit_settings'), 403, 'No autorizado.');
        $this->enforceCompanyAccess($user, $company);

        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $folder = CloudinaryService::companyFolder($company->slug, 'plantilla');
        $result = $cloudinary->upload($request->file('image'), $folder);

        return response()->json([
            'url' => $result['url'],
            'public_id' => $result['public_id'],
        ]);
    }

    /**
     * Extraer solo los valores del schema (sin metadatos de tipo/label)
     */
    private function extractValues(array $schema): array
    {
        $values = [];

        foreach ($schema as $sectionKey => $section) {
            if (!is_array($section)) {
                continue;
            }

            $values[$sectionKey] = [];

            foreach ($section as $fieldKey => $field) {
                // Ignorar campos de metadatos (como _label)
                if (str_starts_with($fieldKey, '_')) {
                    continue;
                }

                if (is_array($field) && isset($field['value'])) {
                    $values[$sectionKey][$fieldKey] = $field['value'];
                }
            }
        }

        return $values;
    }
}
