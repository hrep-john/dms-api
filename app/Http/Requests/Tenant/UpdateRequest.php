<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UpdateRequest extends FormRequest
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
        $id = request('tenant_id');

        return [
            'domain' => ['nullable', 'string', 'max:50', 'unique:tenants,domain,'.$id],
            'name' => ['nullable', 'string', 'max:50', 'unique:tenants,name,'.$id]
        ];
    }
}
