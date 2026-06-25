<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\RevenueTrackingService;
use App\Http\Requests\Report\RevenueTrackingRequest;

class RevenueTrackingController extends Controller
{
    public function __construct(
        private readonly RevenueTrackingService $revenueTrackingService
    ) {}

    public function index(RevenueTrackingRequest $request)
    {
        $data = $this->revenueTrackingService->getData($request);

        return view('admin.reports.revent-tracking', [
            'rows'      => $data['rows'],
            'summary'   => $data['summary'],
            'startDate' => $data['startDate'],
            'endDate'   => $data['endDate'],
        ]);
    }
}
