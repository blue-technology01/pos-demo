<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UomRequest extends FormRequest
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
        $code = $this->route('code'); // route parameter for update/delete
 
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
 
        return [
            'code' => $isUpdate
                ? 'sometimes|string|max:20|unique:uoms,code,' . $code . ',code'
                : 'required|string|max:20|unique:uoms,code',
            'name'        => $isUpdate ? 'sometimes|string|max:100' : 'required|string|max:100',
            'status'      => $isUpdate ? 'sometimes|in:active,inactive' : 'nullable|in:active,inactive',
        ];
    }
}
