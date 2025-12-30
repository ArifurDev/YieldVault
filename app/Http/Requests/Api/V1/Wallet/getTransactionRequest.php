<?php

namespace App\Http\Requests\Api\V1\Wallet;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class getTransactionRequest extends FormRequest
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
            'type'      => ['nullable', new Enum(TransactionType::class)],
            'source'    => ['nullable', new Enum(TransactionSource::class)],
            'status'    => ['nullable', new Enum(TransactionStatus::class)],

            'from_date' => ['nullable', 'date', 'before_or_equal:to_date'],
            'to_date'   => ['nullable', 'date', 'after_or_equal:from_date'],

            'page'      => ['nullable','integer'],
            'per_page'  => ['nullable', 'integer'],
        ];
    }
}
