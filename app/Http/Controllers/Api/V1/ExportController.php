<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportCampaignsRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function exportCampaigns(ExportCampaignsRequest $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'client_id' => $request->input('client_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $path = $this->exportService->exportCampaigns(
            $request->user()->organization_id,
            $filters,
            $request->input('format')
        );

        $downloadUrl = $this->exportService->getDownloadUrl($path);

        return ApiResponse::success([
            'path' => $path,
            'download_url' => $downloadUrl,
            'format' => $request->input('format'),
            'created_at' => now()->toISOString(),
        ], 'Export created successfully');
    }
}
