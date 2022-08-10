<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;

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
        $payloadObjectPath = $this->payload['DocumentLocation']['S3ObjectName'];
        $split = explode('/', $payloadObjectPath);
        $mediaId = $split[1];

        $document = Document::whereHas('media', function ($query) use ($mediaId) {
            $query->where('id', $mediaId);
        })->first();

        $auth = User::find($document->updated_by);
        $jobId = $this->payload['JobId'];

        dispatch(new GetExtractedDocument($document, $auth, $jobId));
    }
}
