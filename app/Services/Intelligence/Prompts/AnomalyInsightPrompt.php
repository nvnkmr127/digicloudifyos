<?php

namespace App\Services\Intelligence\Prompts;

class AnomalyInsightPrompt
{
    public static function build(array $data): string
    {
        $clientName = $data['client_name'];
        $industry = $data['industry'];
        $anomalies = $data['anomalies'];

        $anomalyText = '';
        foreach ($anomalies as $index => $anomaly) {
            $anomalyText .= ($index + 1).". Channel: {$anomaly['channel_type']}, Metric: {$anomaly['metric_name']}, Baseline: {$anomaly['baseline']}, Current: {$anomaly['current']}, Deviation: {$anomaly['deviation']}%\n";
        }

        return <<<PROMPT
You are a senior marketing performance analyst. Analyze these anomalies for {$clientName} ({$industry}):

{$anomalyText}

Return a JSON array of insights with these fields for each entry:
- priority: (critical|high|medium|low|opportunity)
- category: (ad_performance|budget|organic|conversion|opportunity)
- title: clear short title
- issue_description: what happened
- root_cause: likely reason (creative fatigue, seasonaility, platform changes)
- recommended_action: what specifically should be done
- expected_impact: (high|medium|low)
- effort_level: (low|medium|high)

Return ONLY a JSON array.
PROMPT;
    }
}
