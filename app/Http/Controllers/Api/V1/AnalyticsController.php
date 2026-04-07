<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsDashboardRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function dashboard(AnalyticsDashboardRequest $request): JsonResponse
    {
        $period = $request->input('period', '30days');

        $metrics = $this->analyticsService->getDashboardMetrics(
            $request->user()->organization_id,
            $period
        );

        return ApiResponse::success($metrics, null, [
            'period' => $period,
            'generated_at' => now()->toISOString(),
            'units' => [
                'roi_estimate' => 'percent',
                'avg_ctr' => 'percent',
                'avg_conversion_rate' => 'percent',
                'avg_cpc' => 'currency_per_click',
            ],
        ]);
    }
}
