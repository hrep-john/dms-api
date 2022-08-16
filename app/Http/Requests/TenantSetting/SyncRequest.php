<?php

namespace App\Http\Requests\TenantSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class SyncRequest extends FormRequest
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
            'tenant_id' => ['required', 'integer', 'max:50', 'exists:tenants,id'],
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'exists:tenant_settings,key'],
        ];
    }
}
