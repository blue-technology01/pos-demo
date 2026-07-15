<?php

namespace App\Http\Requests\Stock;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WarehouseStoreRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:warehouses,name'
            ],
            'location' => [
                'nullable',
                'string',
                'max:100'
            ],
            'phone' => [
                'nullable',
                'string',
                'unique:warehouses,phone'
            ],
            'is_active' => [
                'boolean'
            ],
        ];
    }
}
