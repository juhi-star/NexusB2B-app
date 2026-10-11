<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Exceptions\EmptyOrderException;
use App\Exceptions\InactiveCustomerException;
use App\Exceptions\InsufficientCreditException;
use App\Services\OrderCreditEvaluator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class SubmitOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('submit', $this->route('order')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Pre-submission credit validation. This is a best-effort guard for fast
     * feedback; the authoritative check runs inside the order service under a
     * database lock.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $order = $this->route('order');
                $order->loadMissing('items');

                if ($order->items->isEmpty()) {
                    $validator->errors()->add('order', EmptyOrderException::cannotSubmit()->getMessage());

                    return;
                }

                try {
                    app(OrderCreditEvaluator::class)->assertSufficient(
                        $order->customer,
                        (float) $order->total_amount,
                    );
                } catch (InactiveCustomerException|InsufficientCreditException $exception) {
                    $validator->errors()->add('order', $exception->getMessage());
                }
            },
        ];
    }
}
