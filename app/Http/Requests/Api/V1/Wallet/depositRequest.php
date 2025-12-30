<?php

namespace App\Http\Requests\Api\V1\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class depositRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount'        => ['required', 'numeric', 'min:1'],
            'proof'         => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'remarks'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
