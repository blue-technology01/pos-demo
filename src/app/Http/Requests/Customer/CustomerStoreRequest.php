<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
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
            'name'    => ['required', 'string', 'max:' . Customer::NAME_MAX_LENGTH],
            'phone'   => ['nullable', 'string', 'unique:customers,phone'],
            'email'   => ['nullable', 'email', 'unique:customers,email'],
            'address' => ['nullable', 'string', 'max:' . Customer::ADDRESS_MAX_LENGTH],
            'status'  => ['sometimes', 'in:' . Customer::STATUS_ACTIVE . ',' . Customer::STATUS_INACTIVE],
            // 'status' => 'required|in:active,inactive',
        ];
    }
}
