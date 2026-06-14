<?php

namespace App\Http\Requests\Sale;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaleItemStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'product_code' => ['required', 'string', 'max:20', 'exists:products,code'],
            'uom_code' => ['nullable', 'string', 'max:20', 'exists:uoms,code'],
            'product_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            // DO NOT trust this, but still validate if sent
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
