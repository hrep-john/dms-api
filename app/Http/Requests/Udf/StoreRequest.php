<?php

namespace App\Http\Requests\Udf;

use App\Enums\EntitableTypes;
use App\Enums\UserDefinedFieldSection;
use App\Enums\UserDefinedFieldType;
use App\Rules\UniqueTenantUdf;
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
        $udfTypes = UserDefinedFieldType::getValues();
        $udfSections = UserDefinedFieldSection::getValues();
        $entitableTypes = EntitableTypes::getValues();

        return [
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'entitable_type' => ['required', 'string', Rule::in($entitableTypes)],
            'label' => ['required', 'string', 'max:255', new UniqueTenantUdf()],
            'key' => ['required', 'string', 'max:255'],
            'section' => ['required', 'integer', Rule::in($udfSections)],
            'type' => ['required', 'integer', Rule::in($udfTypes)],
            'visible' => ['required', 'boolean'],
            'settings' => ['required', 'string']
        ];
    }
}
