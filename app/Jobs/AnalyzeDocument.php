<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $documentId = null;
    public $userId = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $documentId, int $userId)
    {
        $this->documentId = $documentId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(\App\Http\Services\Contracts\DocumentEntityMetadataServiceInterface::class)->analyze($this->documentId, $this->userId);
    }
}
