<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'po_number' => 'required|string|unique:purchase_orders,po_number,' . $this->id,
            'order_date' => 'required|date',
            'status' => 'required|string',
        ];
    }
}
