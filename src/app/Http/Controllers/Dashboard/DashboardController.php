<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /* show dashboard page */
    public function index(): \Illuminate\View\View
    {
        $period = 'today';

        return view('admin.dashboard.index', [
            'initialPeriod' => $period,
            'dashboardData' => $this->getDashboardData($period),
        ]);
    }

    /* return all dashbiard data for the given period */
    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'string', Rule::in(DashboardService::PERIODS)],
        ]);

        $period = $validated['period'] ?? 'today';

        return response()->json($this->getDashboardData($period));
    }

    /* get dashboard data */
    private function getDashboardData(string $period): array
    {
        $key = "dashboard:{$period}:" . now()->format('Y-m-d-H-i'); // minute-based cache

        return Cache::remember($key, 60, function () use ($period) {
            return [
                'stats' => $this->dashboardService->getStats($period),
                'chart' => $this->dashboardService->getColumnChart($period),
                'donut' => $this->dashboardService->getDonutChart($period),
            ];
        });
    }
}
