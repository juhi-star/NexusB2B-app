<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where('status', CustomerStatus::Active->value),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Select the customer this order is for.',
            'customer_id.exists' => 'The selected customer is not active or does not exist.',
            'items.required' => 'An order must contain at least one line item.',
            'items.min' => 'An order must contain at least one line item.',
            'items.*.product_id.required' => 'Each line item must reference a product.',
            'items.*.product_id.distinct' => 'Each product may only appear once per order.',
            'items.*.product_id.exists' => 'One of the selected products is not available.',
            'items.*.quantity.required' => 'Each line item must have a quantity.',
            'items.*.quantity.min' => 'Line item quantities must be at least 1 unit.',
        ];
    }
}
