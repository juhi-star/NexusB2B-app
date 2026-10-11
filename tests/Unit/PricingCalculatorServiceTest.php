<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\VolumeTier;
use App\Services\PricingCalculatorService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PricingCalculatorServiceTest extends TestCase
{
    private PricingCalculatorService $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pricing = new PricingCalculatorService;
    }

    public function test_standard_tier_applies_no_discount(): void
    {
        $result = $this->pricing->calculateLine(100.0, 1);

        $this->assertSame(VolumeTier::Standard, $result->tier);
        $this->assertSame(0.0, $result->discountPercent);
        $this->assertSame(100.0, $result->unitPrice);
        $this->assertSame(100.0, $result->lineTotal);
    }

    public function test_boundary_quantities_map_to_the_correct_tier(): void
    {
        $this->assertSame(VolumeTier::Standard, $this->pricing->calculateLine(100, 49)->tier);
        $this->assertSame(VolumeTier::Bulk, $this->pricing->calculateLine(100, 50)->tier);
        $this->assertSame(VolumeTier::Bulk, $this->pricing->calculateLine(100, 99)->tier);
        $this->assertSame(VolumeTier::Enterprise, $this->pricing->calculateLine(100, 100)->tier);
    }

    public function test_bulk_tier_applies_five_percent_discount(): void
    {
        $result = $this->pricing->calculateLine(100, 50);

        $this->assertSame(5.0, $result->discountPercent);
        $this->assertSame(95.0, $result->unitPrice);
        $this->assertSame(4750.0, $result->lineTotal);
    }

    public function test_enterprise_tier_applies_ten_percent_discount(): void
    {
        $result = $this->pricing->calculateLine(100, 120);

        $this->assertSame(10.0, $result->discountPercent);
        $this->assertSame(90.0, $result->unitPrice);
        $this->assertSame(10800.0, $result->lineTotal);
    }

    public function test_unit_price_is_rounded_to_two_decimals(): void
    {
        $result = $this->pricing->calculateLine(12.25, 100);

        $this->assertSame(11.03, $result->unitPrice);
        $this->assertSame(1103.0, $result->lineTotal);
    }

    public function test_discount_amount_is_reported(): void
    {
        $this->assertSame(250.0, $this->pricing->calculateLine(100, 50)->discountAmount());
    }

    public function test_to_array_exposes_persistable_columns(): void
    {
        $this->assertSame([
            'quantity' => 50,
            'base_unit_price' => 100.0,
            'discount_percent' => 5.0,
            'unit_price' => 95.0,
            'line_total' => 4750.0,
        ], $this->pricing->calculateLine(100, 50)->toArray());
    }

    public function test_quantity_below_one_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pricing->calculateLine(100, 0);
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pricing->calculateLine(-1.0, 5);
    }
}
