<?php

namespace App\Http\Requests\Stock;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WarehouseUpdateRequest extends FormRequest
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
        $warehouseId = $this->route('warehouse')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:warehouses,name,' . $warehouseId,
            ],
            'location' => 'nullable|string|max:100',
            'phone' => [
                'nullable',
                'string',
                'unique:warehouses,phone,' . $warehouseId,
            ],
            'is_active' => 'boolean',
        ];
    }
}
