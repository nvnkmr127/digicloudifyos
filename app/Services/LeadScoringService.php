<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class LeadScoringService
{
    /**
     * Calculate and update the score for a lead based on various criteria.
     */
    public function calculate(Lead $lead): int
    {
        $score = 0;

        // 1. Source Scoring
        $sourceScores = [
            'Facebook' => 10,
            'Google' => 15,
            'Referral' => 25,
            'Website' => 5,
        ];
        $score += $sourceScores[$lead->source] ?? 0;

        // 2. Data Completeness
        if ($lead->email) {
            $score += 5;
        }
        if ($lead->phone) {
            $score += 5;
        }
        if ($lead->company) {
            $score += 10;
        }

        // 3. Status Weight
        $statusWeight = [
            'New' => 0,
            'Qualified' => 20,
            'Nurturing' => 40,
            'Converted' => 100,
        ];
        $score += $statusWeight[$lead->status] ?? 0;

        // 4. Custom Logic (e.g. email domain)
        if (str_contains($lead->email, '.edu') || str_contains($lead->email, '.gov')) {
            $score += 20;
        }

        // Update lead with new score
        $lead->update(['score' => $score]);

        Log::info("Lead Scoring: Lead {$lead->id} scored {$score}");

        return $score;
    }
}
