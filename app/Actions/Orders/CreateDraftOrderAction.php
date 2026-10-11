<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderTotalCalculator;
use App\Services\PricingCalculatorService;
use Illuminate\Support\Facades\DB;

final class CreateDraftOrderAction
{
    public function __construct(
        private readonly PricingCalculatorService $pricing,
        private readonly OrderTotalCalculator $totals,
    ) {}

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $lines
     */
    public function execute(User $salesRepresentative, Customer $customer, array $lines): Order
    {
        return DB::transaction(function () use ($salesRepresentative, $customer, $lines): Order {
            $order = Order::query()->create([
                'customer_id' => $customer->getKey(),
                'created_by' => $salesRepresentative->getKey(),
                'status' => OrderStatus::Draft,
                'total_amount' => 0,
            ]);

            foreach ($lines as $line) {
                $product = Product::query()->findOrFail($line['product_id']);
                $pricing = $this->pricing->calculateLine((float) $product->base_price, (int) $line['quantity']);

                $order->items()->create([
                    'product_id' => $product->getKey(),
                    ...$pricing->toArray(),
                ]);
            }

            return $this->totals->recalculate($order);
        });
    }
}
