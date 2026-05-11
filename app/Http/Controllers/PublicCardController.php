<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;

class PublicCardController extends Controller
{
    /**
     * Perfil público de una empresa con sus tarjetas activas.
     * GET /api/public/{companySlug}
     */
    public function company(string $companySlug): JsonResponse
    {
        $company = Company::where('slug', $companySlug)
            ->with([
                'cards'    => fn($q) => $q->where('is_active', true)->orderBy('last_name'),
                'services' => fn($q) => $q->where('is_active', true),
                'products' => fn($q) => $q->where('is_active', true),
                'settings',
            ])
            ->firstOrFail();

        // Obtener customization con valores por defecto aplicados
        $settings = $company->getOrCreateSettings();

        return response()->json([
            'company' => $company,
            'template' => [
                'name' => $settings->template_name,
                'customization' => $settings->values_only,
            ],
        ]);
    }

    /**
     * Tarjeta de presentación individual.
     * GET /api/public/{companySlug}/{cardSlug}
     */
    public function card(string $companySlug, string $cardSlug): JsonResponse
    {
        $company = Company::where('slug', $companySlug)
            ->with([
                'services' => fn($q) => $q->where('is_active', true),
                'products' => fn($q) => $q->where('is_active', true),
                'settings',
            ])
            ->firstOrFail();

        $card = $company->cards()
            ->where('slug', $cardSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Obtener customization con valores por defecto aplicados
        $settings = $company->getOrCreateSettings();

        return response()->json([
            'card'    => $card,
            'company' => $company,
            'template' => [
                'name' => $settings->template_name,
                'customization' => $settings->values_only,
            ],
        ]);
    }
}
