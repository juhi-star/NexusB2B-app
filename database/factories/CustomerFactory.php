<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->unique()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('555-####'),
            'credit_limit' => 20000,
            'outstanding_balance' => 0,
            'status' => CustomerStatus::Active->value,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => CustomerStatus::Inactive->value]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => CustomerStatus::Suspended->value]);
    }

    public function withCredit(float $limit, float $outstanding = 0): static
    {
        return $this->state(fn (): array => [
            'credit_limit' => $limit,
            'outstanding_balance' => $outstanding,
        ]);
    }
}
