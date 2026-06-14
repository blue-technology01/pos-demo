<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Blade dashboard page.
     */
    public function index(): \Illuminate\View\View
    {
        $period = 'today';

        return view('admin.dashboard.index', [
            'initialPeriod' => $period,
            'dashboardData' => $this->getDashboardData($period),
        ]);
    }

    /**
     * AJAX: return all dashboard data for the given period.
     */
    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'string', Rule::in(DashboardService::PERIODS)],
        ]);

        $period = $validated['period'] ?? 'today';

        return response()->json($this->getDashboardData($period));
    }

    private function getDashboardData(string $period): array
    {
        return Cache::remember(
            "dashboard:{$period}",
            self::CACHE_TTL,
            fn () => [
                'stats' => $this->dashboardService->getStats($period),
                'chart' => $this->dashboardService->getColumnChart($period),
                'donut' => $this->dashboardService->getDonutChart($period),
            ]
        );
    }
}
