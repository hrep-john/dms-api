<?php

namespace App\Http\Requests\TenantSetting;

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
            'tenant_id' => ['required', 'string', 'max:50', 'exists:tenants,id'],
            'key' => ['required', 'string', 'max:100', 'unique:tenant_settings,key,'.$id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:45']
        ];
    }
}
