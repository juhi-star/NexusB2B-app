<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Orders\Concerns\AssertsOrderTransitions;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientInventoryException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderCreditEvaluator;
use Illuminate\Support\Facades\DB;

final class ApproveOrderAction
{
    use AssertsOrderTransitions;

    public function __construct(
        private readonly OrderCreditEvaluator $credit,
    ) {}

    public function execute(Order $order, User $manager): Order
    {
        return DB::transaction(function () use ($order, $manager): Order {
            $order = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->ensureTransition($order, OrderStatus::Approved);

            $order->load('items');

            $customer = Customer::query()->whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
            $this->credit->assertSufficient($customer, (float) $order->total_amount);

            // Lock every product row in a deterministic order to avoid deadlocks.
            $products = Product::query()
                ->whereIn('id', $order->items->pluck('product_id')->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate all lines before mutating anything.
            foreach ($order->items as $item) {
                $product = $products->get($item->product_id);

                if ($product->stock_quantity < $item->quantity) {
                    throw InsufficientInventoryException::forProduct(
                        $product,
                        $item->quantity,
                        $product->stock_quantity,
                    );
                }
            }

            foreach ($order->items as $item) {
                $product = $products->get($item->product_id);

                $product->update([
                    'stock_quantity' => $product->stock_quantity - $item->quantity,
                ]);
            }

            $customer->update([
                'outstanding_balance' => round(
                    (float) $customer->outstanding_balance + (float) $order->total_amount,
                    2,
                ),
            ]);

            $order->update([
                'status' => OrderStatus::Approved,
                'approved_by' => $manager->getKey(),
                'approved_at' => now(),
            ]);

            return $order->refresh();
        });
    }
}
