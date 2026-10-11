<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReviewOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->input('decision') === 'reject' ? 'reject' : 'approve';

        return $this->user()?->can($ability, $this->route('order')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'remarks' => ['nullable', 'string', 'max:1000', 'required_if:decision,reject'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Select whether to approve or reject the order.',
            'decision.in' => 'The decision must be either approve or reject.',
            'remarks.required_if' => 'A rejection reason is required when rejecting an order.',
            'remarks.max' => 'Remarks may not be longer than 1000 characters.',
        ];
    }
}
