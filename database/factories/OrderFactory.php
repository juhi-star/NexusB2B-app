<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'created_by' => User::factory(),
            'status' => OrderStatus::Draft->value,
            'total_amount' => 0,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => ['status' => OrderStatus::Submitted->value]);
    }

    public function approved(?User $manager = null): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Approved->value,
            'approved_by' => $manager?->getKey() ?? User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(?User $manager = null, string $reason = 'Credit limit exceeded.'): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Rejected->value,
            'rejection_reason' => $reason,
            'rejected_by' => $manager?->getKey() ?? User::factory(),
            'rejected_at' => now(),
        ]);
    }

    public function dispatched(?User $manager = null): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Dispatched->value,
            'approved_by' => $manager?->getKey() ?? User::factory(),
            'approved_at' => now(),
            'dispatched_at' => now(),
        ]);
    }
}
