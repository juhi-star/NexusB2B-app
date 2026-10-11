<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\VolumeTier;
use PHPUnit\Framework\TestCase;

final class VolumeTierTest extends TestCase
{
    public function test_for_quantity_resolves_tier_boundaries(): void
    {
        $this->assertSame(VolumeTier::Standard, VolumeTier::forQuantity(1));
        $this->assertSame(VolumeTier::Standard, VolumeTier::forQuantity(49));
        $this->assertSame(VolumeTier::Bulk, VolumeTier::forQuantity(50));
        $this->assertSame(VolumeTier::Bulk, VolumeTier::forQuantity(99));
        $this->assertSame(VolumeTier::Enterprise, VolumeTier::forQuantity(100));
        $this->assertSame(VolumeTier::Enterprise, VolumeTier::forQuantity(1000));
    }

    public function test_discount_percentages(): void
    {
        $this->assertSame(0.0, VolumeTier::Standard->discountPercent());
        $this->assertSame(5.0, VolumeTier::Bulk->discountPercent());
        $this->assertSame(10.0, VolumeTier::Enterprise->discountPercent());
    }

    public function test_min_and_max_quantities(): void
    {
        $this->assertSame(1, VolumeTier::Standard->minQuantity());
        $this->assertSame(49, VolumeTier::Standard->maxQuantity());
        $this->assertSame(50, VolumeTier::Bulk->minQuantity());
        $this->assertSame(99, VolumeTier::Bulk->maxQuantity());
        $this->assertSame(100, VolumeTier::Enterprise->minQuantity());
        $this->assertNull(VolumeTier::Enterprise->maxQuantity());
    }
}
