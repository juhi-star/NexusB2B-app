<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Orders\ApproveOrderAction;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientInventoryException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_with_insufficient_stock_aborts_without_corrupting_state(): void
    {
        $manager = User::factory()->creditManager()->create();
        $customer = Customer::factory()->withCredit(100000)->create();

        $scarce = Product::factory()->priced(100)->withStock(5)->create();
        $plentiful = Product::factory()->priced(100)->withStock(100)->create();

        $order = Order::factory()->submitted()->create([
            'customer_id' => $customer->id,
            'total_amount' => 3000,
        ]);

        // First line is satisfiable, second is not — validation must run before any mutation.
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $plentiful->id,
            'quantity' => 20,
            'base_unit_price' => 100,
            'unit_price' => 100,
            'line_total' => 2000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $scarce->id,
            'quantity' => 10,
            'base_unit_price' => 100,
            'unit_price' => 100,
            'line_total' => 1000,
        ]);

        try {
            app(ApproveOrderAction::class)->execute($order, $manager);
            $this->fail('Expected InsufficientInventoryException.');
        } catch (InsufficientInventoryException) {
            // expected
        }

        $this->assertSame(100, $plentiful->fresh()->stock_quantity, 'No stock should have been decremented.');
        $this->assertSame(5, $scarce->fresh()->stock_quantity);
        $this->assertSame(0.0, (float) $customer->fresh()->outstanding_balance, 'Balance must be unchanged.');
        $this->assertSame(OrderStatus::Submitted, $order->fresh()->status);
    }
}
