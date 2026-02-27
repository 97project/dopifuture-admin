<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('class'));
    }

    public function rules(): array
    {
        return [
            'school_id'     => 'required|exists:schools,id',
            'name'          => 'required|string|max:100',
            'grade_level'   => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:10',
            'is_active'     => 'nullable|boolean',
        ];
    }
}
