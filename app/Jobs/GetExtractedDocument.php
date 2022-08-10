<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetExtractedDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $document = null;
    public $authUser = null;
    public $jobId = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($document, $authUser, $jobId)
    {
        $this->document = $document;
        $this->authUser = $authUser;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(\App\Http\Services\Contracts\DocumentDetailMetadataServiceInterface::class)->getResults($this->document, $this->authUser, $this->jobId);
    }
}
