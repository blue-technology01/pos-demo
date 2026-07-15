<?php

namespace App\Http\Requests\Stock;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAjustmentUpdateRequest extends FormRequest
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
            'product_code'    => ['sometimes', 'required', 'string', 'exists:products,code'],
            'warehouse_id'    => ['sometimes', 'required', 'integer', 'exists:warehouses,id'],
            'adjustment_date' => ['sometimes', 'required', 'date'],
            'new_quantity'    => ['sometimes', 'required', 'integer', 'min:0'],
            'reason_code'     => ['sometimes', 'required', 'string', Rule::in(['damage', 'break', 'other'])],
            'remark'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_code.exists'  => 'The selected product does not exist.',
            'warehouse_id.exists'  => 'The selected warehouse does not exist.',
            'reason_code.in'       => 'Invalid reason code. Allowed values: damage, break, other.',
        ];
    }
}
