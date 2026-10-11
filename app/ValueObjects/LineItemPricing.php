<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\VolumeTier;

final readonly class LineItemPricing
{
    public function __construct(
        public int $quantity,
        public float $baseUnitPrice,
        public float $discountPercent,
        public float $unitPrice,
        public float $lineTotal,
        public VolumeTier $tier,
    ) {}

    public function discountAmount(): float
    {
        return round(($this->baseUnitPrice - $this->unitPrice) * $this->quantity, 2);
    }

    /**
     * @return array<string, int|float>
     */
    public function toArray(): array
    {
        return [
            'quantity' => $this->quantity,
            'base_unit_price' => $this->baseUnitPrice,
            'discount_percent' => $this->discountPercent,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
        ];
    }
}
