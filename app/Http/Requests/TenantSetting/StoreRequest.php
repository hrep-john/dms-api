<?php

namespace App\Http\Requests\TenantSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tenant_id' => ['required', 'string', 'max:50', 'exists:tenants,id'],
            'key' => ['required', 'string', 'max:100', 'unique:tenant_settings,key'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:45']
        ];
    }
}
