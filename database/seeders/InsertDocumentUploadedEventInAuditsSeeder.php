<?php

namespace Database\Seeders;

use App\Enums\Event;
use App\Traits\Seedable;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Seeder;
use OwenIt\Auditing\Models\Audit;

class InsertDocumentUploadedEventInAuditsSeeder extends Seeder
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

        foreach(Audit::where('event', Event::Created)->where('auditable_type', 'App\\Models\\Document')->cursor() as $audit) {
            $data = [
                'user_type' => $audit->user_type,
                'user_id' => $audit->user_id,
                'event' => Event::Uploaded,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'old_values' => [],
                'new_values' => [],
                'url' => $audit->url,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'tags' => $audit->tags,
                'created_at' => Carbon::parse($audit->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($audit->updated_at)->format('Y-m-d H:i:s'),
            ];

            Audit::create($data);
        }

        $this->seed($this::class);
    }
}
