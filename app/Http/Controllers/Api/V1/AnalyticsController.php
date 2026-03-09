<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $period = $request->input('period', '30days');

        $validPeriods = ['7days', '30days', '90days', 'thisMonth', 'lastMonth', 'thisYear'];

        if (! in_array($period, $validPeriods)) {
            return response()->json([
                'message' => 'Invalid period',
                'valid_periods' => $validPeriods,
            ], 400);
        }

        $metrics = $this->analyticsService->getDashboardMetrics(
            $request->user()->organization_id,
            $period
        );

        return response()->json([
            'data' => $metrics,
            'period' => $period,
            'generated_at' => now()->toISOString(),
        ]);
    }
}
