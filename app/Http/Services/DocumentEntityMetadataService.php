<?php   

namespace App\Http\Services;

use App;
use App\Http\Services\Contracts\DocumentEntityMetadataServiceInterface;
use App\Http\Services\Contracts\DocumentServiceInterface;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use App\Models\DocumentEntityMetadata;
use Artisan;
use Aws\Comprehend\ComprehendClient;

class DocumentEntityMetadataService extends BaseService implements DocumentEntityMetadataServiceInterface
{
    /**      
     * @var Model      
     */     
    protected $model;
    protected $sdk;
    protected $userId;

    /**      
     * DocumentEntityMetadataService constructor.      
     *      
     * @param DocumentEntityMetadata $model      
     */     
    public function __construct(DocumentEntityMetadata $model)
    {
        parent::__construct($model);
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

    public function analyze(int $documentId, $userId)
    {
        $this->userId = $userId;
        $this->initialize(ComprehendClient::class);
        $document = App::make(DocumentServiceInterface::class)->find($documentId);
        $results = $this->getAllEntities($document);
        $this->bulkInsert($document, $results);

        return $results;
    }

    protected function formatAttributes($attributes, $method): array
    {
        return $attributes;
    }

    protected function getAllEntities(Document $model)
    {
        $data = [];
        $details = $model->detailMetadata->chunk(50);

        foreach($details as $detail) {
            $detail = $detail->pluck('text')->implode(' ');
            $results = $this->sdk->BatchDetectEntities([
                'TextList' => [$detail],
                'LanguageCode' => 'en'
            ]);

            foreach($results['ResultList'] as $result) {
                $data = array_merge($data, $result['Entities']);
            }
        }

        return $data;
    }

    protected function bulkInsert(Document $model, $results)
    {
        $model->entityMetadata()->delete();
        $results = collect($results)->chunk(250);

        foreach($results as $result) {
            $formatted = $this->mappings($result, $model->id);
            $model->entityMetadata()->insert($formatted);
        }

        $model->entityMetadata()->searchable();
        $model->searchable();
    }

    protected function mappings($data, $modelId)
    {
        return $data->map(function($item) use ($modelId) {
            return [
                'document_id' => $modelId,
                'text' => $item['Text'],
                'type' => $item['Type'],
                'score' => $item['Score'],
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId
            ];
        })->toArray();
    }
}