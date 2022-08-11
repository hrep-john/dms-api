<?php

namespace App\Jobs;

use App;
use App\Http\Services\Contracts\DocumentServiceInterface;
use App\Http\Services\Contracts\UserServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TextractTopicToSqs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subject = null;
    public $payload = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $subject, array $payload)
    {
        $this->subject = $subject;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $objectPath = $this->payload['DocumentLocation']['S3ObjectName'];
        $split = explode('/', $objectPath);
        $mediaId = $split[1];

        $document = App::make(DocumentServiceInterface::class)->findDocumentByMediaId($mediaId);
        $user = App::make(UserServiceInterface::class)->find($document->updated_by);
        $jobId = $this->payload['JobId'];

        dispatch(new GetExtractedDocument($document->id, $user->id, $jobId));
    }
}
