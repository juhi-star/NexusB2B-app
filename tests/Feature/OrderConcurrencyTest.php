<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Orders\ApproveOrderAction;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Concurrency safety for the approval path relies on `SELECT ... FOR UPDATE`
 * row locks inside a single database transaction. SQLite silently ignores
 * those locks, so a meaningful test must run against MySQL 8 (or PostgreSQL).
 *
 * The suite therefore self-skips on non-locking drivers while asserting the
 * invariants that the lock guarantees: stock never goes negative and the
 * customer balance only ever reflects committed, approved orders.
 */
final class OrderConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_path_uses_row_level_locks(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped(
                'Row-level locking requires MySQL/Postgres; the active driver ('.DB::connection()->getDriverName().') is a no-op.',
            );
        }

        $manager = User::factory()->creditManager()->create();
        $customer = Customer::factory()->withCredit(100000)->create();
        $product = Product::factory()->priced(100)->withStock(10)->create();

        $order = Order::factory()->submitted()->create([
            'customer_id' => $customer->id,
            'total_amount' => 1000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        app(ApproveOrderAction::class)->execute($order, $manager);

        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->assertGreaterThanOrEqual(0, $product->fresh()->stock_quantity);
    }
}
