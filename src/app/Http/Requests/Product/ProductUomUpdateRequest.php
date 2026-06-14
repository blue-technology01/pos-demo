<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUomUpdateRequest extends FormRequest
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
            'product_code' => [
                'required',
                'string',
                'exists:products,code',
            ],

            'uom_code' => [
                'required',
                'string',
                'exists:uoms,code',
                Rule::unique('product_uoms')
                    ->where(function ($query) {
                        return $query->where('product_code', $this->product_code);
                    })
                    ->ignore($this->route('id')),
            ],

            'quantity_per_unit' => [
                'required',
                'numeric',
                'min:1',
            ],

            'cost_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],
            'uom_role' => [
                'required',
                'string',
                Rule::in(['retail', 'bulk', 'alternative']),
            ],
        ];
    }

}
