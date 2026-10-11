<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Enums\OrderStatus;
use App\Exceptions\EmptyOrderException;
use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderCreditEvaluator;
use Illuminate\Support\Facades\DB;

final class SubmitOrderAction
{
    use AssertsOrderTransitions;

    public function __construct(
        private readonly OrderCreditEvaluator $credit,
    ) {}

    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureTransition($order, OrderStatus::Submitted);

            $order->load('items');

            if ($order->items->isEmpty()) {
                throw EmptyOrderException::cannotSubmit();
            }

            $customer = Customer::query()->whereKey($order->customer_id)->lockForUpdate()->firstOrFail();

            $this->credit->assertSufficient($customer, (float) $order->total_amount);

            $order->update(['status' => OrderStatus::Submitted]);

            return $order->refresh();
        });
    }
}
