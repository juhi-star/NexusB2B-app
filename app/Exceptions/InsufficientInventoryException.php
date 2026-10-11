<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

final class InsufficientInventoryException extends RuntimeException
{
    public static function forProduct(Product $product, int $requested, int $available): self
    {
        return new self(sprintf(
            'Insufficient stock for product "%s": requested %d, available %d.',
            $product->name,
            $requested,
            $available,
        ));
    }
}
