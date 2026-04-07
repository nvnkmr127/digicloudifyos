<?php

use App\Http\Controllers\Api\V1\AgencyController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\SocialListeningIngestController;
use App\Http\Controllers\Api\V1\WorkloadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function () {
    Route::post('social-listening/ingest', [SocialListeningIngestController::class, 'ingest'])
        ->name('social-listening.ingest');

    Route::middleware(['auth:sanctum', 'organization'])->group(function () {
        Route::apiResource('campaigns', CampaignController::class);

        Route::get('campaigns/{campaign}/metrics', [CampaignController::class, 'metrics'])
            ->name('campaigns.metrics');

        Route::post('exports/campaigns', [ExportController::class, 'exportCampaigns'])
            ->name('exports.campaigns');

        Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard'])
            ->name('analytics.dashboard');

        Route::get('workload/analysis', [WorkloadController::class, 'analysis'])
            ->name('workload.analysis');

        Route::get('agency/dashboard', [AgencyController::class, 'dashboard'])
            ->name('agency.dashboard');
    });
});
