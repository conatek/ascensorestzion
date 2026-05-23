<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_clients'      => Client::count(),
            'total_sites'        => Site::count(),
            'total_equipment'    => Equipment::count(),
            'active_equipment'   => Equipment::where('status', 'activo')->count(),
            'total_users'        => User::where('active', true)->count(),
            'equipment_by_type'  => Equipment::selectRaw('equipment_type, count(*) as count')
                ->groupBy('equipment_type')->get(),
            'equipment_by_status' => Equipment::selectRaw('status, count(*) as count')
                ->groupBy('status')->get(),
        ]);
    }
}
