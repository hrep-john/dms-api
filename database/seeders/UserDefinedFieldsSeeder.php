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
            'document_number'       => UserDefinedFieldType::Number,
            'subject'               => UserDefinedFieldType::Text,
            'action'                => UserDefinedFieldType::Text,
            'date_received_from'    => UserDefinedFieldType::Date,
            'date_received_to'      => UserDefinedFieldType::Date
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
