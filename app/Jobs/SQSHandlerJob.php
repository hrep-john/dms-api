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
use Illuminate\Contracts\Queue\Job as LaravelJob;

class SQSHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * @param LaravelJob $job
     * @param array $data
     */
    public function handle(LaravelJob $job, $data)
    {
        // This is incoming JSON payload, already decoded to an array
        $datatype = GETTYPE($data);

        if ($datatype === 'string') {
            if (isset($data)) {
                $data = JSON_DECODE($data, true);

                if (isset($data['Type'])) {
                    $this->dispatchGetExtractedDocument($data['MessageId'], JSON_DECODE($data['Message'], true));
                }
            }
        }
    }

    protected function dispatchGetExtractedDocument($subject, $payload)
    {
        $payloadObjectPath = $payload['DocumentLocation']['S3ObjectName'];
        $split = explode('/', $payloadObjectPath);
        $mediaId = $split[1];

        $document = Document::whereHas('media', function ($query) use ($mediaId) {
            $query->where('id', $mediaId);
        })->first();

        $auth = User::find($document->updated_by);
        $jobId = $payload['JobId'];

        dispatch(new GetExtractedDocument($document, $auth, $jobId));
    }
}
