<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InactiveCustomerException;
use App\Exceptions\InsufficientCreditException;
use App\Models\Customer;

final class OrderCreditEvaluator
{
    public function availableCredit(Customer $customer): float
    {
        return round(
            (float) $customer->credit_limit - (float) $customer->outstanding_balance,
            2,
        );
    }

    public function assertSufficient(Customer $customer, float $orderTotal): void
    {
        if (! $customer->isActive()) {
            throw InactiveCustomerException::forCustomer($customer);
        }

        $available = $this->availableCredit($customer);

        if ($orderTotal > $available) {
            throw InsufficientCreditException::forCustomer($customer, $orderTotal, $available);
        }
    }
}
