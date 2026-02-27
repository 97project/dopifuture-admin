<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        $id = $this->route('role')->id;
        return [
            'name'          => 'required|string|max:50|unique:roles,name,' . $id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
