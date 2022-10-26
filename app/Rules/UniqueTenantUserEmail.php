<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UniqueTenantUserEmail implements Rule
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

        $model = User::with(['user_info' => function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }])
            ->where('email', $value)
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
        return sprintf('The email has already been taken [%s: %s].', $this->attribute, $this->value);
    }
}
