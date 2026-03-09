<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function exportCampaigns(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'format' => ['required', 'in:xlsx,xls,csv,pdf'],
            'status' => ['nullable', 'string'],
            'client_id' => ['nullable', 'uuid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after:date_from'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
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

            return response()->json([
                'message' => 'Export created successfully',
                'data' => [
                    'path' => $path,
                    'download_url' => $downloadUrl,
                    'format' => $request->input('format'),
                    'created_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Export failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
