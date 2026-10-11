<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderTotalCalculator;
use App\Services\PricingCalculatorService;
use Illuminate\Support\Facades\DB;

final class UpdateOrderItemAction
{
    use AssertsOrderTransitions;

    public function __construct(
        private readonly PricingCalculatorService $pricing,
        private readonly OrderTotalCalculator $totals,
    ) {}

    public function execute(Order $order, Product $product, int $quantity): Order
    {
        return DB::transaction(function () use ($order, $product, $quantity): Order {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureDraft($order);

            $item = $order->items()->where('product_id', $product->getKey())->firstOrFail();

            $pricing = $this->pricing->calculateLine((float) $product->base_price, $quantity);

            $item->update($pricing->toArray());

            return $this->totals->recalculate($order);
        });
    }
}
