<?php

namespace App\Services;

class KpiCalculator
{
    public function percent(float $numerator, float $denominator): ?float
    {
        if ($denominator == 0.0) {
            return 0.0;
        }

        return ($numerator / $denominator) * 100.0;
    }

    public function ratio(float $numerator, float $denominator): ?float
    {
        if ($denominator == 0.0) {
            return null;
        }

        return $numerator / $denominator;
    }

    public function ctrPercent(float $clicks, float $impressions): ?float
    {
        return $this->percent($clicks, $impressions);
    }

    public function conversionRatePercent(float $conversions, float $clicks): ?float
    {
        return $this->percent($conversions, $clicks);
    }

    public function cpc(float $spend, float $clicks): ?float
    {
        return $this->ratio($spend, $clicks);
    }

    public function cpm(float $spend, float $impressions): ?float
    {
        if ($impressions == 0.0) {
            return null;
        }

        return ($spend / $impressions) * 1000.0;
    }

    public function cpl(float $spend, float $leads): ?float
    {
        return $this->ratio($spend, $leads);
    }

    public function roas(float $revenue, float $spend): ?float
    {
        return $this->ratio($revenue, $spend);
    }

    public function roiPercent(float $revenue, float $spend): ?float
    {
        if ($spend == 0.0) {
            return null;
        }

        return (($revenue - $spend) / $spend) * 100.0;
    }
}
