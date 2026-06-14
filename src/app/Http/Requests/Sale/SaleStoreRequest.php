<?php

namespace App\Http\Requests\Sale;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // sale data
            'invoice_no'      => ['required', 'string', 'max:50', 'unique:sales,invoice_no'],
            'sale_date'       => ['required', 'date_format:Y-m-d'],
            'payment_method'  => ['required', 'string', Rule::in(['cash', 'card', 'qr'])],
            'status'          => ['required', 'string', Rule::in(['pending', 'completed'])],
            'note'            => ['nullable', 'string', 'max:500'],

            // Finanace total
            'sub_total'       => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'tax_amount'      => ['required', 'numeric', 'min:0'],
            'total_amount'    => ['required', 'numeric', 'min:0'],
            'paid_amount'     => ['required', 'numeric', 'gte:total_amount'],
            'change_amount'   => ['required', 'numeric', 'min:0', 'lte:paid_amount'],

            // cart item
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.product_code'        => ['required', 'string', 'exists:products,code'],
            'items.*.product_name'        => ['required', 'string', 'max:150'],
            'items.*.uom_code'            => ['required', 'string', 'exists:uoms,code'],
            'items.*.quantity'            => ['required', 'numeric', 'gt:0'],
            'items.*.cost_price'          => ['required', 'numeric', 'min:0'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['required', 'numeric', 'between:0,100'],
            'items.*.discount_amount'     => ['required', 'numeric', 'min:0'],
            'items.*.amount'              => ['required', 'numeric', 'min:0'],
        ];
    }
}
