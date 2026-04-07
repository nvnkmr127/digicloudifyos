<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AgencyManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    public function __construct(
        protected AgencyManagementService $agencyService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $dashboard = $this->agencyService->getAgencyDashboard(
            $request->user()->organization_id
        );

        return ApiResponse::success($dashboard, null, [
            'generated_at' => now()->toISOString(),
        ]);
    }
}
