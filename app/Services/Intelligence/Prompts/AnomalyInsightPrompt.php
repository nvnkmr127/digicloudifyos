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
            $anomalyText .= ($index + 1).". Channel: " . json_encode($anomaly['channel_type']) . 
                            ", Metric: " . json_encode($anomaly['metric_name']) . 
                            ", Baseline: " . json_encode($anomaly['baseline']) . 
                            ", Current: " . json_encode($anomaly['current']) . 
                            ", Deviation: " . json_encode($anomaly['deviation']) . "%\n";
        }

        $safeClientName = json_encode($clientName);
        $safeIndustry = json_encode($industry);

        return <<<PROMPT
[SYSTEM INSTRUCTION]
You are a senior marketing performance analyst. Your task is to analyze performance anomalies and provide actionable insights.
DATA SOURCE: { "client": {$safeClientName}, "industry": {$safeIndustry} }

ANOMALIES TO ANALYZE:
{$anomalyText}

[RESPONSE FORMAT]
Return a JSON array of insights. Each insight must follow this schema:
{
  "priority": "critical|high|medium|low|opportunity",
  "category": "ad_performance|budget|organic|conversion|opportunity",
  "title": "Clear short title",
  "issue_description": "What specifically happened",
  "root_cause": "Likely reason (creative fatigue, seasonality, platform changes)",
  "recommended_action": "Specific optimization steps",
  "expected_impact": "high|medium|low",
  "effort_level": "low|medium|high"
}

IMPORTANT: Return ONLY the JSON array. Do not include markdown code blocks or additional text.
PROMPT;
    }
}
