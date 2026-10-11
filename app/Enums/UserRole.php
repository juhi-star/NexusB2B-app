<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SalesRepresentative = 'sales_rep';

    case CreditManager = 'credit_manager';

    public function label(): string
    {
        return match ($this) {
            self::SalesRepresentative => 'Sales Representative',
            self::CreditManager => 'Credit Manager',
        };
    }

    public function isCreditManager(): bool
    {
        return $this === self::CreditManager;
    }

    public function isSalesRepresentative(): bool
    {
        return $this === self::SalesRepresentative;
    }
}
