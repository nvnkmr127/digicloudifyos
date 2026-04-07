<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkloadAnalysisRequest;
use App\Http\Responses\ApiResponse;
use App\Services\WorkloadAnalysisService;
use Illuminate\Http\JsonResponse;

class WorkloadController extends Controller
{
    public function __construct(
        protected WorkloadAnalysisService $workloadService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function analysis(WorkloadAnalysisRequest $request): JsonResponse
    {
        $period = $request->input('period', 'current_week');

        $analysis = $this->workloadService->getTeamWorkload(
            $request->user()->organization_id,
            $period
        );

        return ApiResponse::success($analysis, null, [
            'period' => $period,
            'generated_at' => now()->toISOString(),
        ]);
    }
}
