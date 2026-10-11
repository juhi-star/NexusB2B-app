<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Orders\ApproveOrderAction;
use App\Enums\OrderStatus;
use App\Exceptions\InactiveCustomerException;
use App\Exceptions\InsufficientCreditException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderCreditEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderCreditTest extends TestCase
{
    use RefreshDatabase;

    private function rep(): User
    {
        return User::factory()->salesRepresentative()->create();
    }

    private function manager(): User
    {
        return User::factory()->creditManager()->create();
    }

    public function test_available_credit_is_limit_minus_outstanding(): void
    {
        $customer = Customer::factory()->withCredit(5000, 1200)->create();

        $this->assertSame(3800.0, app(OrderCreditEvaluator::class)->availableCredit($customer));
    }

    public function test_submission_is_blocked_when_the_order_exceeds_available_credit(): void
    {
        $rep = $this->rep();
        $customer = Customer::factory()->withCredit(1000)->create();
        $product = Product::factory()->priced(100)->withStock(100)->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $rep->id,
            'total_amount' => 2000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'base_unit_price' => 100,
            'unit_price' => 100,
            'line_total' => 2000,
        ]);

        $this->actingAs($rep)
            ->post(route('orders.submit', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame(OrderStatus::Draft, $order->fresh()->status);
        $this->assertSame(100, $product->fresh()->stock_quantity);
    }

    public function test_approval_throws_and_rolls_back_when_credit_became_insufficient(): void
    {
        $manager = $this->manager();
        $customer = Customer::factory()->withCredit(1000)->create();
        $product = Product::factory()->priced(100)->withStock(100)->create();

        $order = Order::factory()->submitted()->create([
            'customer_id' => $customer->id,
            'total_amount' => 2000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        try {
            app(ApproveOrderAction::class)->execute($order, $manager);
            $this->fail('Expected InsufficientCreditException.');
        } catch (InsufficientCreditException) {
            // expected
        }

        $this->assertSame(OrderStatus::Submitted, $order->fresh()->status);
        $this->assertSame(100, $product->fresh()->stock_quantity);
        $this->assertSame(0.0, (float) $customer->fresh()->outstanding_balance);
    }

    public function test_approval_throws_when_the_customer_is_inactive(): void
    {
        $manager = $this->manager();
        $customer = Customer::factory()->inactive()->create();
        $product = Product::factory()->priced(100)->withStock(100)->create();

        $order = Order::factory()->submitted()->create([
            'customer_id' => $customer->id,
            'total_amount' => 100,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->expectException(InactiveCustomerException::class);

        app(ApproveOrderAction::class)->execute($order, $manager);
    }
}
