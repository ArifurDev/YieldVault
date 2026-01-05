<?php

namespace App\Http\Requests\Api\V1\Manager\Wallet;

use App\Enum\Wallet\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateRequest extends FormRequest
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
            'type'           => ['required', new Enum(AccountType::class)],
            'label'          => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:255'],
            'network'        => ['nullable', 'string', 'max:50'],
            'is_active'      => ['required', 'boolean'],
        ];
    }
}
