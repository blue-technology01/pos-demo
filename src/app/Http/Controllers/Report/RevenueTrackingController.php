<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\RevenueTrackingRequest;
use App\Services\Report\RevenueTrackingService;

class RevenueTrackingController extends Controller
{
    public function __construct(
        private readonly RevenueTrackingService $revenueTrackingService
    ) {}

    public function index(RevenueTrackingRequest $request)
    {
        $data = $this->revenueTrackingService->getData(
            startDate: $request->input('start_date'),
            endDate:   $request->input('end_date'),
            search:    $request->input('search'),
            perPage:   (int) $request->input('per_page', 25),
            page:      (int) $request->input('page', 1),
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($data);
        }

        return view('admin.reports.revent-tracking', $data);
    }
}
