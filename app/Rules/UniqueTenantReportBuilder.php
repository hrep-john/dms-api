<?php

namespace App\Rules;

use App\Models\ReportBuilder;
use Illuminate\Contracts\Validation\Rule;

class UniqueTenantReportBuilder implements Rule
{
    private $attribute = '';
    private $value = '';
    private $id = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id = null)
    {
        $this->id = $id;
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

        $tenantId = \App\Helpers\tenant();
        $id = $this->id;

        $model = ReportBuilder::where('tenant_id', $tenantId)
            ->where('name', $value)
            ->when(!is_null($id), function ($query) use ($id) {
                return $query->where('id', '<>', $id);
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
        return sprintf('The name has already been taken [%s: %s].', $this->attribute, $this->value);
    }
}
