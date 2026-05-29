<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending_payment,paid,payment_failed'],
            'payment_method' => ['nullable', 'in:stripe,paypal,transferencia,efectivo'],
        ];
    }
}