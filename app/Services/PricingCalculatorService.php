<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VolumeTier;
use App\ValueObjects\LineItemPricing;
use InvalidArgumentException;

final class PricingCalculatorService
{
    public function calculateLine(float $baseUnitPrice, int $quantity): LineItemPricing
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Line item quantity must be at least 1.');
        }

        if ($baseUnitPrice < 0) {
            throw new InvalidArgumentException('Base unit price cannot be negative.');
        }

        $tier = VolumeTier::forQuantity($quantity);
        $discountPercent = $tier->discountPercent();
        $baseUnitPrice = round($baseUnitPrice, 2);
        $unitPrice = round($baseUnitPrice * (1 - $discountPercent / 100), 2);
        $lineTotal = round($unitPrice * $quantity, 2);

        return new LineItemPricing(
            quantity: $quantity,
            baseUnitPrice: $baseUnitPrice,
            discountPercent: $discountPercent,
            unitPrice: $unitPrice,
            lineTotal: $lineTotal,
            tier: $tier,
        );
    }
}
