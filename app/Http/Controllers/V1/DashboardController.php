<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'dashboard' => $this->fetchDashboardData($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->fetchDashboardData($request),
        ]);
    }

    private function fetchDashboardData(Request $request): array
    {
        $proxy = $request->duplicate();
        $proxy->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = app(HomeController::class)->index($proxy);
        $payload = json_decode($response->getContent(), true);

        return $payload['data'] ?? [];
    }
}
