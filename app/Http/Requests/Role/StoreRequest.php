<?php

namespace App\Http\Requests\Role;

use App\Rules\UniqueTenantRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', new UniqueTenantRole()],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', 'max:255', 'exists:permissions,name'],
        ];
    }

    public function messages()
    {
        return [
            'permissions.*.exists' => 'The permission name should exist in the permissions table.'
        ];
    }
}
