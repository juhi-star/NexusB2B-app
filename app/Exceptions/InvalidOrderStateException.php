<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\OrderStatus;
use RuntimeException;

final class InvalidOrderStateException extends RuntimeException
{
    public static function cannotTransition(OrderStatus $from, OrderStatus $to): self
    {
        return new self(sprintf(
            'Order cannot transition from "%s" to "%s".',
            $from->value,
            $to->value,
        ));
    }

    public static function notEditable(OrderStatus $current): self
    {
        return new self(sprintf(
            'Order in "%s" state can no longer be modified.',
            $current->value,
        ));
    }
}
