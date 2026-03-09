<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkloadAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkloadController extends Controller
{
    public function __construct(
        protected WorkloadAnalysisService $workloadService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function analysis(Request $request): JsonResponse
    {
        $period = $request->input('period', 'current_week');

        $validPeriods = ['current_week', 'next_week', 'current_month', 'next_month', 'current_quarter'];

        if (! in_array($period, $validPeriods)) {
            return response()->json([
                'message' => 'Invalid period',
                'valid_periods' => $validPeriods,
            ], 400);
        }

        $analysis = $this->workloadService->getTeamWorkload(
            $request->user()->organization_id,
            $period
        );

        return response()->json([
            'data' => $analysis,
            'period' => $period,
            'generated_at' => now()->toISOString(),
        ]);
    }
}
