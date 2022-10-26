<?php

namespace Database\Seeders;

use App\Enums\AllowUserAccess;
use App\Models\Document;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;

class UpdateAllowUserAccessExistingDocumentsSeeder extends Seeder
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

        foreach(Document::cursor() as $document) {
            $value = $document->allow_user_access 
                ? AllowUserAccess::YesAllowSelectedUsers 
                : AllowUserAccess::NoDontAllow;

            Document::where('id', $document->id)->update(['allow_user_access' => $value]);
        }

        $this->seed($this::class);
    }
}
