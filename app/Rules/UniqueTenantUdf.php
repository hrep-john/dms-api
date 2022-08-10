<?php

namespace App\Rules;

use App\Models\UserDefinedField;
use Illuminate\Contracts\Validation\Rule;

class UniqueTenantUdf implements Rule
{
    private $attribute = '';
    private $value = '';
    private $udfId = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($udfId = null)
    {
        $this->udfId = $udfId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        $this->value = $value;

        $tenantId = request('tenant_id');
        $udfId = $this->udfId;

        $model = UserDefinedField::where('tenant_id', $tenantId)
            ->where('label', $value)
            ->when(!is_null($udfId), function ($query) use ($udfId) {
                return $query->where('id', '<>', $udfId);
            });

        return $model->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf('Tenant UDF should be unique [%s: %s].', $this->attribute, $this->value);
    }
}
