<?php

declare(strict_types=1);

namespace App\Enums;

enum VolumeTier: string
{
    case Standard = 'standard';

    case Bulk = 'bulk';

    case Enterprise = 'enterprise';

    public function minQuantity(): int
    {
        return match ($this) {
            self::Standard => 1,
            self::Bulk => 50,
            self::Enterprise => 100,
        };
    }

    public function maxQuantity(): ?int
    {
        return match ($this) {
            self::Standard => 49,
            self::Bulk => 99,
            self::Enterprise => null,
        };
    }

    public function discountPercent(): float
    {
        return match ($this) {
            self::Standard => 0.0,
            self::Bulk => 5.0,
            self::Enterprise => 10.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard (1-49 units)',
            self::Bulk => 'Bulk (50-99 units)',
            self::Enterprise => 'Enterprise (100+ units)',
        };
    }

    public static function forQuantity(int $quantity): self
    {
        return match (true) {
            $quantity >= self::Enterprise->minQuantity() => self::Enterprise,
            $quantity >= self::Bulk->minQuantity() => self::Bulk,
            default => self::Standard,
        };
    }
}
