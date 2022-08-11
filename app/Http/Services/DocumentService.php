<?php   

namespace App\Http\Services;

use App;
use App\Enums\ExtractableOcr;
use App\Http\Resources\DocumentBasicResource;
use App\Http\Services\Contracts\DocumentServiceInterface;
use App\Jobs\ExtractDocument;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use Arr;
use MeiliSearch\Endpoints\Indexes;
use Storage;
use Str;
use IRR;

class DocumentService extends BaseService implements DocumentServiceInterface
{
    /**      
     * @var Model      
     */     
    protected $model;

    /**      
     * DocumentService constructor.      
     *      
     * @param Document $model      
     */     
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }

    public function paginate() 
    {
        $perPage = request()->get('per_page', 10);
        $q = request()->get('q', null);

        $builder = $this->model;

        if ($q) {
            $builder = $builder->search($q);
        }

        $builder = $builder->orderBy('updated_at', 'desc')->paginate($perPage);

        return $builder;
    }

    public function search() 
    {
        $perPage = request()->get('per_page', 10);
        $page = request()->get('page', 1);
        $q = request()->get('q', '');
        $filter = request()->get('filter', null);
        $sort = request()->get('sort', null);

        if (is_null($q)) {
            $q = '';
        }

        $builder = $this->model->search($q, function(Indexes $meiliSearch, string $query, array $options) use ($filter, $sort, $perPage, $page) {
            if ($filter) {
                $options['filter'] = $filter;
            }

            if ($sort) {
                $options['sort'] = [$sort];
            }

            $options['attributesToHighlight'] = ['*'];

            $options['limit'] = $perPage;
            $options['offset'] = ($page - 1) * $perPage;
            $options['attributesToCrop'] = ['formatted_detail_metadata', 'detail_metadata'];
            $options['cropLength'] = 10;
            $options['attributesToRetrieve'] = [
                'id',
                'file_name',
                'file_extension',
                'file_size',
                'user_defined_field',
                'formatted_udfs',
                'formatted_updated_at',
                'formatted_detail_metadata',
                'created_by',
                'updated_by',
            ];

            return $meiliSearch->search($query, $options);
        })->raw();

        $results = collect($builder['hits'])->map(function ($hit) {
            $collection = [];
            $requiredFields = [
                'file_name',
                'file_size',
                'file_extension',
                'formatted_detail_metadata'
            ];

            // GET Match Results for Required Fields
            $formattedFields = Arr::only($hit['_formatted'], $requiredFields);
            $matchFields = $this->selectMatchFields($formattedFields);
            $collection = array_merge($collection, $matchFields);

            // GET Match Results for UDF Metadata
            $formattedFields = $hit['_formatted']['formatted_udfs'];
            $matchFields = $this->selectMatchFields($formattedFields);
            $collection = array_merge($collection, $matchFields);

            $mappings = [
                'id' => $hit['id'],
                'file_name' => $hit['file_name'],
                'user_defined_field' => $hit['user_defined_field'],
                'updated_at' => $hit['formatted_updated_at'],
                'match' => $collection,
                'created_by' => $hit['created_by'],
                'updated_by' => $hit['updated_by'],
            ];

            return $mappings;
        })->toArray();

        $results = $this->model->hydrate($results);

        return [
            'data' => DocumentBasicResource::collection($results),
            'meta' => $this->getMetaFromHits($builder)
        ];
    }

    protected function getMetaFromHits($hits)
    {
        $perPage = $hits['limit'];
        $total = $hits['nbHits'];
        $currentPage = ( $hits['offset'] / $perPage ) + 1;
        $lastPage = ceil($total / $perPage);

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'to' => ($currentPage * $perPage) > $total ? $total : ($currentPage * $perPage),
            'from' => $hits['offset'] + 1
        ];
    }

    private function selectMatchFields($data) 
    {
        $fields = [];

        foreach ($data as $field => $value) {
            if (Str::containsAll($value, ['<em>', '</em>'])) {
                $fields[] = [
                    'field' => $field,
                    'value' => $value
                ];
            }
        }

        return $fields;
    }

    public function download(int $id)
    {
        $document = $this->find($id);

        return Storage::disk('s3')->temporaryUrl(  
            $document->latest_media->getPath(),
            now()->addMinutes(1),
            ['ResponseContentDisposition' => 'attachment']
        );
    }

    public function upload($attributes): Document
    {
        $that = $this;

        return $this->transaction(function() use ($attributes, $that) {
            $mappings = [
                'folder_id' => $that->getFolderId(),
                'file_name' => $attributes['document']->getClientOriginalName()
            ];

            $count = $that->checkFilenameCount($mappings['file_name']);

            if ($count > 0) {
                $document = $that->model->where('file_name', $mappings['file_name'])->first();
                // WEIRD ISSUE:
                // NOT WORKING $document->update(['updated_by' => auth()->user()->id]);
                // TEMPORARY FIXED
                $document->updated_by = auth()->user()->id;
                $document->updated_at = now();
                $document->save();
            } else {
                $document = $that->store($mappings);
            }

            $file = $document
                ->addMedia($attributes['document'])
                ->withCustomProperties([
                    'version' => $count + 1
                ])
                ->toMediaCollection('files');

            $document->searchable();

            $extractable = array(
                ExtractableOcr::Pdf,
                ExtractableOcr::Tiff,
                ExtractableOcr::Png,
                ExtractableOcr::Jpeg
            );

            if (in_array($file->mime_type, $extractable)) {
                dispatch(new ExtractDocument($file->getPath(), auth()->user()->id));
            }

            return $document;
        });
    }

    public function findDocumentByMediaId(int $mediaId) 
    {
        $document = Document::whereHas('media', function ($query) use ($mediaId) {
            $query->where('id', $mediaId);
        });

        return $document->first();
    }

    protected function checkFilenameCount(string $file_name = '')
    {
        $count = 0; 

        $builder = $this->model->withCount('media')->where('file_name', $file_name);

        if ($builder->count() > 0) {
            $document = $builder->first();
            $count = $document->media_count;
        }

        return $count;
    }

    protected function formatAttributes($attributes): array
    {
        $attributes['user_defined_field'] = JSON_ENCODE($attributes['user_defined_field'] ?? []);

        return $attributes;
    }

    protected function afterDelete($model): void
    {
        if ($model->detailMetadata()->count() > 0) {
            $model->detailMetadata()->delete();
            $model->detailMetadata()->unsearchable();
        }

        if ($model->entityMetadata()->count() > 0) {
            $model->entityMetadata()->delete();
            $model->entityMetadata()->unsearchable();
        }
    }

    private function getFolderId() 
    {
        $tenant = auth()->user()->userInfo->tenant;
        $folder = $tenant->folders->first();

        return $folder->id;
    }
}