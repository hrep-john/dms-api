<?php   

namespace App\Http\Services;

use App;
use App\Enums\JobStatus;
use App\Http\Services\Contracts\DocumentDetailMetadataServiceInterface;
use App\Http\Services\Contracts\DocumentServiceInterface;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use App\Models\DocumentDetailMetadata;
use Aws\Textract\TextractClient;
use App\Jobs\AnalyzeDocument;

class DocumentDetailMetadataService extends BaseService implements DocumentDetailMetadataServiceInterface
{
    /**      
     * @var Model      
     */     
    protected $model;
    protected $sdk;
    protected $userId;

    /**      
     * DocumentDetailMetadataService constructor.      
     *      
     * @param DocumentDetailMetadata $model      
     */     
    public function __construct(DocumentDetailMetadata $model)
    {
        parent::__construct($model);
    }

    public function extract(string $filePath, int $userId)
    {
        $this->userId = $userId;
        $this->initialize(TextractClient::class);
        $this->startJob($filePath);
    }

    public function getResults(int $documentId, int $userId, string $jobId) {
        $this->initialize(TextractClient::class);
        $this->userId = $userId;
        $delayPerJob = now()->addSeconds(1);

        $results = $this->getJobResults($jobId);
        $this->bulkInsert($documentId, $results);

        dispatch(new AnalyzeDocument($documentId, $userId))->delay($delayPerJob);
    }

    protected function initialize($service): void
    {
        $this->sdk = $service::factory(array(
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version'  => 'latest',
        ));
    }

    protected function startJob(string $filePath): string
    {
        $result = $this->sdk->startDocumentTextDetection([
            'DocumentLocation' => [
                'S3Object' => [
                    'Bucket' => env('AWS_BUCKET'),
                    'Name' => $filePath
                ],
            ],
            'NotificationChannel' => [
                'RoleArn' =>  env('TEXTRACT_SERVICE_ROLE'), // REQUIRED
                'SNSTopicArn' => env('TEXTRACT_SNS_TOPIC'), // REQUIRED
            ],
        ]);

        return $result['JobId'];
    }

    protected function getJobResults(string $jobId)
    {
        $results = [];
        $nextToken = '';

        if (!$this->verifyIfJobComplete($jobId)) {
            return $results;
        }

        $response = $this->sdk->getDocumentTextDetection(['JobId' => $jobId]);
        $nextToken = $response['NextToken'];
        $results = array_merge($results, $this->filterBlockResult($response['Blocks'], 'LINE'));

        while (!is_null($nextToken)) {
            $response = $this->sdk->getDocumentTextDetection(['JobId' => $jobId, 'NextToken' => $nextToken]);
            $nextToken = $response['NextToken'];
            $results = array_merge($results, $this->filterBlockResult($response['Blocks'], 'LINE'));
        }

        return $results;
    }

    protected function verifyIfJobComplete($jobId)
    {
        $status = JobStatus::InProgress;

        while($status == JobStatus::InProgress) {
            $response = $this->sdk->getDocumentTextDetection(['JobId' => $jobId]);
            $status = $response['JobStatus'];
            sleep(4);
        }

        return $status == JobStatus::Succeeded;
    }

    protected function filterBlockResult($blocks, $blockType)
    {
        $results = collect($blocks)
            ->where('BlockType', $blockType)
            ->map(function($block) {
                return collect($block)->only(['Confidence', 'Text', 'Page']);
            })
            ->toArray();

        return $results;
    }

    protected function bulkInsert($documentId, $results)
    {
        $document = App::make(DocumentServiceInterface::class)->find($documentId);
        $document->detailMetadata()->delete();

        $results = collect($results)->chunk(250);

        foreach($results as $result) {
            $formatted = $this->mappings($result, $document->id);

            $document->detailMetadata()->insert($formatted);
        }
    }

    protected function mappings($data, $modelId)
    {
        return $data->map(function($item) use ($modelId) {
            return [
                'document_id' => $modelId,
                'text' => $item['Text'],
                'score' => $item['Confidence'],
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ];
        })->toArray();
    }
}