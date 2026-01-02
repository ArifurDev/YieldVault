<?php

namespace App\Http\Requests\Api\V1\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'type'          => "required|string|in:nid,passport",
            'file_side_1'   => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
            'file_side_2'   => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ];
    }
}
