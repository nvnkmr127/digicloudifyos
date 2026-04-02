<?php

namespace App\Services\Intelligence\Prompts;

class OpportunityInsightPrompt
{
    public static function build(array $data): string
    {
        $clientName = $data['client_name'];
        $industry = $data['industry'];
        $snapshots = $data['snapshots'];

        $snapshotText = '';
        foreach ($snapshots as $snap) {
            $snapshotText .= "- Channel: {$snap['channel_type']}, Date: {$snap['snapshot_date']}, Spend: \${$snap['spend']}, Conversions: {$snap['conversions']}, ROAS: {$snap['roas']}\n";
        }

        return <<<PROMPT
You are a senior growth marketing expert. Analyze the 7-day performance data for {$clientName} ({$industry}):

{$snapshotText}

Spot growth opportunities, such as:
1. Scaling high-performing campaigns with strong ROAS.
2. Increasing budget on best-converting days.
3. Identifying underutilized channels.

Return a JSON array of insights with these fields:
- priority: (medium|low|opportunity)
- category: opportunity
- title: clear short title
- issue_description: the growth opportunity observed
- recommended_action: tactical steps to capture $5,000+ in extra value
- expected_impact: (high|medium|low)
- effort_level: (low|medium|high)

Return ONLY a JSON array.
PROMPT;
    }
}
