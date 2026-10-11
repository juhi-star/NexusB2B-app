<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;

final class OrderTotalCalculator
{
    public function recalculate(Order $order): Order
    {
        $total = (float) $order->items()->sum('line_total');

        $order->forceFill(['total_amount' => round($total, 2)])->save();

        return $order->refresh();
    }
}
