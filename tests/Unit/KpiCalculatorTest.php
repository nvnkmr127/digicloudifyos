<?php

namespace Tests\Unit;

use App\Services\KpiCalculator;
use Tests\TestCase;

class KpiCalculatorTest extends TestCase
{
    public function test_ctr_percent(): void
    {
        $kpi = new KpiCalculator;
        $this->assertEqualsWithDelta(2.5, $kpi->ctrPercent(25, 1000), 0.0001);
        $this->assertSame(0.0, $kpi->ctrPercent(0, 0));
    }

    public function test_conversion_rate_percent(): void
    {
        $kpi = new KpiCalculator;
        $this->assertEqualsWithDelta(4.0, $kpi->conversionRatePercent(2, 50), 0.0001);
        $this->assertSame(0.0, $kpi->conversionRatePercent(1, 0));
    }

    public function test_roas_and_roi(): void
    {
        $kpi = new KpiCalculator;
        $this->assertEqualsWithDelta(2.0, $kpi->roas(200, 100), 0.0001);
        $this->assertEqualsWithDelta(100.0, $kpi->roiPercent(200, 100), 0.0001);
        $this->assertNull($kpi->roiPercent(10, 0));
    }
}

