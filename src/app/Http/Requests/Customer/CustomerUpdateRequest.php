<?php

    namespace App\Http\Requests\Customer;

    use App\Models\Customer;
    use Illuminate\Contracts\Validation\ValidationRule;
    use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

    class CustomerUpdateRequest extends FormRequest
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
            $customerId = $this->route('customer')->id;

            return [
                'name'    => ['required', 'string', 'max:' . Customer::NAME_MAX_LENGTH],
                'phone'   => ['nullable', 'string', Rule::unique('customers', 'phone')->ignore($customerId)],
                'email'   => ['nullable', 'email',  Rule::unique('customers', 'email')->ignore($customerId)],
                'address' => ['nullable', 'string', 'max:' . Customer::ADDRESS_MAX_LENGTH],
                'status'  => ['required', 'in:active,inactive'],
            ];
        }
    }
