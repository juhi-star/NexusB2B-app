<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Orders\ApproveOrderAction;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStateException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderWorkflowTest extends TestCase
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

    public function test_a_rep_can_create_a_draft_order(): void
    {
        $rep = $this->rep();
        $customer = Customer::factory()->withCredit(10000)->create();
        $product = Product::factory()->priced(100)->withStock(100)->create();

        $response = $this->actingAs($rep)->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ]);

        $order = Order::sole();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame(OrderStatus::Draft, $order->status);
        $this->assertSame($rep->id, $order->created_by);
        $this->assertSame(500.0, (float) $order->total_amount);
        $this->assertCount(1, $order->items);
        $this->assertSame(100, $product->fresh()->stock_quantity, 'Drafts must not touch stock.');
    }

    public function test_the_full_lifecycle_updates_stock_balance_and_audit_trail(): void
    {
        $rep = $this->rep();
        $manager = $this->manager();
        $customer = Customer::factory()->withCredit(10000)->create();
        $product = Product::factory()->priced(100)->withStock(100)->create();

        $this->actingAs($rep)->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ]);

        $order = Order::sole();

        $this->actingAs($rep)->post(route('orders.submit', $order))->assertRedirect();
        $this->assertSame(OrderStatus::Submitted, $order->fresh()->status);

        $this->actingAs($manager)
            ->post(route('orders.review', $order), ['decision' => 'approve'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Approved, $order->status);
        $this->assertSame($manager->id, $order->approved_by);
        $this->assertNotNull($order->approved_at);
        $this->assertSame(95, $product->fresh()->stock_quantity);
        $this->assertSame(500.0, (float) $customer->fresh()->outstanding_balance);

        $this->actingAs($manager)->post(route('orders.dispatch', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Dispatched, $order->status);
        $this->assertNotNull($order->dispatched_at);
    }

    public function test_a_manager_can_reject_a_submitted_order_with_a_reason(): void
    {
        $manager = $this->manager();
        $order = Order::factory()->submitted()->create();

        $this->actingAs($manager)
            ->post(route('orders.review', $order), [
                'decision' => 'reject',
                'remarks' => 'Exceeds the approved credit limit.',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Rejected, $order->status);
        $this->assertSame('Exceeds the approved credit limit.', $order->rejection_reason);
        $this->assertSame($manager->id, $order->rejected_by);
        $this->assertNotNull($order->rejected_at);
    }

    public function test_rejecting_without_a_reason_is_rejected_by_validation(): void
    {
        $manager = $this->manager();
        $order = Order::factory()->submitted()->create();

        $this->actingAs($manager)
            ->post(route('orders.review', $order), ['decision' => 'reject'])
            ->assertSessionHasErrors('remarks');

        $this->assertSame(OrderStatus::Submitted, $order->fresh()->status);
    }

    public function test_an_empty_order_cannot_be_submitted(): void
    {
        $rep = $this->rep();
        $order = Order::factory()->create(['created_by' => $rep->id]);

        $this->actingAs($rep)
            ->post(route('orders.submit', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame(OrderStatus::Draft, $order->fresh()->status);
    }

    public function test_an_order_cannot_be_approved_twice(): void
    {
        $manager = $this->manager();
        $order = Order::factory()->submitted()->create();

        app(ApproveOrderAction::class)->execute($order, $manager);

        $this->expectException(InvalidOrderStateException::class);
        app(ApproveOrderAction::class)->execute($order->fresh(), $manager);
    }
}
