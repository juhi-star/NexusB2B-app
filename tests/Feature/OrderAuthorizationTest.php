<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderAuthorizationTest extends TestCase
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

    public function test_a_sales_rep_cannot_approve_or_reject_orders(): void
    {
        $rep = $this->rep();
        $order = Order::factory()->submitted()->create();

        $this->actingAs($rep)
            ->post(route('orders.review', $order), ['decision' => 'approve'])
            ->assertForbidden();

        $this->actingAs($rep)
            ->post(route('orders.review', $order), ['decision' => 'reject', 'remarks' => 'no'])
            ->assertForbidden();
    }

    public function test_a_manager_cannot_create_orders(): void
    {
        $manager = $this->manager();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($manager)->get(route('orders.create'))->assertForbidden();

        $this->actingAs($manager)->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertForbidden();
    }

    public function test_a_sales_rep_cannot_view_another_reps_order(): void
    {
        $owner = $this->rep();
        $intruder = $this->rep();
        $order = Order::factory()->create(['created_by' => $owner->id]);

        $this->actingAs($intruder)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_a_manager_can_view_any_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->manager())->get(route('orders.show', $order))->assertOk();
    }

    public function test_a_sales_rep_cannot_modify_another_reps_draft(): void
    {
        $owner = $this->rep();
        $intruder = $this->rep();
        $order = Order::factory()->create(['created_by' => $owner->id]);
        $product = Product::factory()->create();

        $this->actingAs($intruder)->post(route('orders.items.store', $order), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertForbidden();
    }

    public function test_a_submitted_order_can_no_longer_be_modified(): void
    {
        $rep = $this->rep();
        $order = Order::factory()->submitted()->create(['created_by' => $rep->id]);
        $product = Product::factory()->create();

        $this->actingAs($rep)->post(route('orders.items.store', $order), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertForbidden();
    }
}
