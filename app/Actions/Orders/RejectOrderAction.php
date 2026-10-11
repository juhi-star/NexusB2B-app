<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RejectOrderAction
{
    use AssertsOrderTransitions;

    public function execute(Order $order, User $manager, string $reason): Order
    {
        return DB::transaction(function () use ($order, $manager, $reason): Order {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureTransition($order, OrderStatus::Rejected);

            $order->update([
                'status' => OrderStatus::Rejected,
                'rejection_reason' => $reason,
                'rejected_by' => $manager->getKey(),
                'rejected_at' => now(),
            ]);

            return $order->refresh();
        });
    }
}
