<?php

namespace Database\Seeders;

use App\Enums\Module;
use App\Enums\UserDefinedFieldSection;
use App\Enums\UserDefinedFieldType;
use App\Models\Tenant;
use App\Models\UserDefinedField;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;

class UserDefinedFieldsSeeder extends Seeder
{
    use Seedable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->hasSeeder($this::class)) {
            return false;
        }

        foreach(Tenant::withoutGlobalScopes()->cursor() as $tenant) {
            $this->generateUserDefinedFields($tenant);
        }

        $this->seed($this::class);
    }

    private function generateUserDefinedFields($tenant) 
    {
        $fields = [
            'doc_number'        => UserDefinedFieldType::Number,
            'date_received'     => UserDefinedFieldType::Date,
            'origin'            => UserDefinedFieldType::Text,
            'text_as_filed'     => UserDefinedFieldType::Text,
            'subject'           => UserDefinedFieldType::Text,
            'action_taken'      => UserDefinedFieldType::Text,
            'date_released'     => UserDefinedFieldType::Date
        ];

        foreach ($fields as $field => $type) {
            UserDefinedField::create([
                'tenant_id'         => $tenant->id,
                'entitable_type'    => Module::Document,
                'label'             => ucwords(str_replace('_', ' ', $field)),
                'key'               => $field,
                'section'           => UserDefinedFieldSection::List,
                'type'              => $type,
                'visible'           => true,
                'settings'          => "{}"
            ]);
        }
    }
}
