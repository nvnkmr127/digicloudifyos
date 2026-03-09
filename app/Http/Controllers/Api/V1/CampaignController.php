<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = [
            'status' => $request->input('status', 'all'),
            'client_id' => $request->input('client_id'),
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $campaigns = $this->campaignService->getAllForOrganization(
            $request->user()->organization_id,
            $filters
        );

        return CampaignResource::collection($campaigns);
    }

    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->create($request->validated());

        return response()->json([
            'message' => 'Campaign created successfully',
            'data' => new CampaignResource($campaign),
        ], 201);
    }

    public function show(Request $request, Campaign $campaign): CampaignResource
    {
        $this->authorize('view', $campaign);

        if ($campaign->organization_id !== $request->user()->organization_id) {
            abort(404);
        }

        return new CampaignResource($campaign->load([
            'client',
            'adAccount',
            'tasks',
            'creativeRequests',
            'alerts',
        ]));
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->organization_id !== $request->user()->organization_id) {
            abort(404);
        }

        $campaign = $this->campaignService->update($campaign, $request->validated());

        return response()->json([
            'message' => 'Campaign updated successfully',
            'data' => new CampaignResource($campaign),
        ]);
    }

    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('delete', $campaign);

        if ($campaign->organization_id !== $request->user()->organization_id) {
            abort(404);
        }

        $this->campaignService->delete($campaign);

        return response()->json([
            'message' => 'Campaign deleted successfully',
        ], 204);
    }

    public function metrics(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        if ($campaign->organization_id !== $request->user()->organization_id) {
            abort(404);
        }

        $metrics = $this->campaignService->getMetrics($campaign);

        return response()->json([
            'data' => $metrics,
        ]);
    }
}
