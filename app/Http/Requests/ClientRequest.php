<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'company_name'    => ['required', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:500'],
            'industry'        => ['nullable', 'string', 'max:255'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}
