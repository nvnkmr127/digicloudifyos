<?php

use App\Http\Controllers\Api\V1\AgencyController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\WorkloadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('campaigns', CampaignController::class);

        Route::get('campaigns/{campaign}/metrics', [CampaignController::class, 'metrics'])
            ->name('api.v1.campaigns.metrics');

        Route::post('exports/campaigns', [ExportController::class, 'exportCampaigns'])
            ->name('api.v1.exports.campaigns');

        Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard'])
            ->name('api.v1.analytics.dashboard');

        Route::get('workload/analysis', [WorkloadController::class, 'analysis'])
            ->name('api.v1.workload.analysis');

        Route::get('agency/dashboard', [AgencyController::class, 'dashboard'])
            ->name('api.v1.agency.dashboard');
    });
});

