<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $document = null;
    public $authUser = null;
    public $delaySeconds = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($document, $authUser)
    {
        $this->document = $document;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(\App\Http\Services\Contracts\DocumentDetailMetadataServiceInterface::class)->extract($this->document, $this->authUser);
    }
}
