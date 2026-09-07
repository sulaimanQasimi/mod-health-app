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
        // Instant shell: visibility only. Stats + charts load via API after mount.
        return Inertia::render('Dashboard', [
            'dashboard' => $this->fetchDashboardData($request, 'meta'),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $section = $request->input('section', 'all');

        return response()->json([
            'success' => true,
            'data' => $this->fetchDashboardData($request, $section),
        ]);
    }

    private function fetchDashboardData(Request $request, string $section = 'all'): array
    {
        $proxy = $request->duplicate();
        $proxy->headers->set('X-Requested-With', 'XMLHttpRequest');
        $proxy->query->set('section', $section);

        if ($request->filled('chart_branch_id')) {
            $proxy->query->set('chart_branch_id', $request->input('chart_branch_id'));
        }

        $response = app(HomeController::class)->index($proxy);
        $payload = json_decode($response->getContent(), true);

        return $payload['data'] ?? [];
    }
}
