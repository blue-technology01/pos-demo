<?php

namespace App\Http\Requests\Cash;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CashRegisterStoreRequest extends FormRequest
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
        // if close shift is need closing_balance every time
        if ($this->isMethod('put') || $this->isMethod('patch') || $this->has('closing_balance')) {
            return [
                'closing_balance' => 'required|numeric|min:0',
                'note'            => 'nullable|string|max:500',
            ];
        }

        // if open shift is need opening_balance every thime
        return [
            'opening_balance' => 'required|numeric|min:0',
        ];
    }
}
