<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Customer;
use RuntimeException;

final class InactiveCustomerException extends RuntimeException
{
    public static function forCustomer(Customer $customer): self
    {
        return new self(sprintf(
            'Customer "%s" has a "%s" account and cannot place orders.',
            $customer->company_name,
            $customer->status->value,
        ));
    }
}
