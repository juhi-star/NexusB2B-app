<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderTotalCalculator;
use App\Services\PricingCalculatorService;
use Illuminate\Support\Facades\DB;

final class AddOrderItemAction
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

            $pricing = $this->pricing->calculateLine((float) $product->base_price, $quantity);

            $order->items()->updateOrCreate(
                ['product_id' => $product->getKey()],
                $pricing->toArray(),
            );

            return $this->totals->recalculate($order);
        });
    }
}
