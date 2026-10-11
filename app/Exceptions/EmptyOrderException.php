<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class EmptyOrderException extends RuntimeException
{
    public static function cannotSubmit(): self
    {
        return new self('An order must contain at least one line item before it can be submitted.');
    }
}
