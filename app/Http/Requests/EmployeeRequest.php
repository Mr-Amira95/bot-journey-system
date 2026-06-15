<?php

namespace App\Http\Requests;

use App\Enums\EmployeeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'manager_id'    => ['nullable', 'integer', 'exists:employees,id'],
            'position'      => ['required', 'string', 'max:255'],
            'hire_date'     => ['required', 'date', 'before_or_equal:today'],
            'type'          => ['required', Rule::enum(EmployeeType::class)],
            'salary'        => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(fn () => $this->input('type') === EmployeeType::ContractEmployee->value),
            ],
            'hourly_rate'   => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(fn () => $this->input('type') === EmployeeType::HourlyEmployee->value),
            ],
        ];
    }
}
