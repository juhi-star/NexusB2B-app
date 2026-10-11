<?php

declare(strict_types=1);

namespace App\Actions\Orders\Concerns;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStateException;
use App\Models\Order;

trait AssertsOrderTransitions
{
    protected function ensureTransition(Order $order, OrderStatus $target): void
    {
        if (! $order->status->canTransitionTo($target)) {
            throw InvalidOrderStateException::cannotTransition($order->status, $target);
        }
    }

    protected function ensureDraft(Order $order): void
    {
        if ($order->status !== OrderStatus::Draft) {
            throw InvalidOrderStateException::notEditable($order->status);
        }
    }
}
