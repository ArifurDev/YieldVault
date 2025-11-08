<?php

namespace App\Http\Requests\Api\V1\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class indexRequest extends FormRequest
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
            'page'     => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'status'    => 'nullable|in:Pending,Approved,Rejected',
            'sort_by'   => 'nullable|in:created_at,updated_at,status,document_type',
            'sort_dir'  => 'nullable|in:asc,desc',
        ];
    }
}
