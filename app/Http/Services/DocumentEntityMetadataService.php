<?php   

namespace App\Http\Services;

use App\Http\Services\Contracts\DocumentEntityMetadataServiceInterface;
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
    protected $auth;

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

    public function analyze(Document $model, $auth)
    {
        $this->auth = $auth;

        $this->initialize(ComprehendClient::class);
        $results = $this->getAllEntities($model);
        $this->bulkInsert($model, $results);

        return $results;
    }

    protected function formatAttributes($attributes): array
    {
        return $attributes;
    }

    protected function getAllEntities($model)
    {
        $data = [];
        $details = $model->detailMetadata->chunk(500);

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

    protected function bulkInsert($model, $results)
    {
        $model->entityMetadata()->delete();
        $results = collect($results)->chunk(500);

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
                'created_by' => $this->auth->id,
                'updated_by' => $this->auth->id
            ];
        })->toArray();
    }
}