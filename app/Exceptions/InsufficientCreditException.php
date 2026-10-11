<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Customer;
use RuntimeException;

final class InsufficientCreditException extends RuntimeException
{
    public static function forCustomer(Customer $customer, float $orderTotal, float $available): self
    {
        return new self(sprintf(
            'Order total %.2f exceeds the available credit of %.2f for customer "%s".',
            $orderTotal,
            $available,
            $customer->company_name,
        ));
    }
}
