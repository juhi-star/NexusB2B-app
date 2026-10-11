<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_lines_receive_tiered_pricing_and_roll_up_into_the_total(): void
    {
        $rep = User::factory()->salesRepresentative()->create();
        $customer = Customer::factory()->withCredit(100000)->create();
        $product = Product::factory()->priced(100)->withStock(1000)->create();

        $this->actingAs($rep)->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 60],
            ],
        ]);

        $order = Order::sole();
        $item = $order->items->sole();

        $this->assertSame(60, $item->quantity);
        $this->assertSame(5.0, (float) $item->discount_percent);
        $this->assertSame(95.0, (float) $item->unit_price);
        $this->assertSame(5700.0, (float) $item->line_total);
        $this->assertSame(5700.0, (float) $order->total_amount);
    }

    public function test_multiple_lines_are_summed_into_the_order_total(): void
    {
        $rep = User::factory()->salesRepresentative()->create();
        $customer = Customer::factory()->withCredit(100000)->create();
        $bulk = Product::factory()->priced(100)->withStock(1000)->create();
        $standard = Product::factory()->priced(50)->withStock(1000)->create();

        $this->actingAs($rep)->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $bulk->id, 'quantity' => 100],      // 10% -> 90 * 100 = 9000
                ['product_id' => $standard->id, 'quantity' => 10],   //  0% -> 50 * 10  = 500
            ],
        ]);

        $order = Order::sole();

        $this->assertCount(2, $order->items);
        $this->assertSame(9500.0, (float) $order->total_amount);
    }
}
