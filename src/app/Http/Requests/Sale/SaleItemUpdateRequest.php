<?php

namespace App\Http\Requests\Sale;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaleItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Usually NOT editable, but kept if you allow reassignment
            'sale_id' => ['sometimes', 'integer', 'exists:sales,id'],
            'product_code' => ['sometimes', 'string', 'max:20', 'exists:products,code'],
            'uom_code' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:uoms,code'],
            'product_name' => ['sometimes', 'string', 'max:150'],
            'quantity' => ['sometimes', 'numeric', 'min:0.01'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'discount_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            // Still optional + never trusted fully
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'Quantity must be greater than 0.',
            'unit_price.min' => 'Unit price cannot be negative.',
            'discount_percentage.max' => 'Discount cannot exceed 100%.',
        ];
    }
}
