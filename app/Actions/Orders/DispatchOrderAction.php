<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

final class DispatchOrderAction
{
    use AssertsOrderTransitions;

    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureTransition($order, OrderStatus::Dispatched);

            $order->update([
                'status' => OrderStatus::Dispatched,
                'dispatched_at' => now(),
            ]);

            return $order->refresh();
        });
    }
}
