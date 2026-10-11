<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerStatus: string
{
    case Active = 'active';

    case Suspended = 'suspended';

    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Inactive => 'Inactive',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
