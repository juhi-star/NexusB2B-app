<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderTotalCalculator;
use Illuminate\Support\Facades\DB;

final class RemoveOrderItemAction
{
    use AssertsOrderTransitions;

    public function __construct(
        private readonly OrderTotalCalculator $totals,
    ) {}

    public function execute(Order $order, Product $product): Order
    {
        return DB::transaction(function () use ($order, $product): Order {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureDraft($order);

            $order->items()->where('product_id', $product->getKey())->firstOrFail()->delete();

            return $this->totals->recalculate($order);
        });
    }
}
