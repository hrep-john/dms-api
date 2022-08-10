<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Http\Requests\Document\BulkDestroyRequest;
use App\Http\Requests\Document\RevertRequest;
use App\Models\Document as MainModel;
use App\Http\Resources\DocumentBasicResource as BasicResource;
use App\Http\Services\Contracts\DocumentServiceInterface;
use App\Http\Requests\Document\StoreRequest;
use App\Http\Requests\Document\UpdateRequest;
use App\Http\Resources\AuditLogBasicResource;
use App\Http\Resources\DocumentAutocompleteResource;
use App\Http\Resources\DocumentFullResource;
use App\Models\BaseModel;
use SplFileInfo;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Exception;
use Illuminate\Http\Request;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    protected $service;

    public function __construct(DocumentServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return BasicResource
     */
    public function index()
    {
        $results = $this->service->paginate();
        $results->data = BasicResource::collection($results);

        return $this->success([
            'results' => $this->paginate($results)
        ], Response::HTTP_OK);
    }

    /**
     * Display search results of the given query.
     *
     * @return Resource
     */
    public function search()
    {
        $results = $this->service->search();

        return $this->success([
            'results' => $results
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return DocumentFullResource
     */
    public function store(StoreRequest $request)
    {
        try {
            $result = $this->service->upload($request->validated());
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.save.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new DocumentFullResource($result),
            'message' => Lang::get('success.uploaded')
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return DocumentFullResource
     */
    public function show(int $id)
    {
        $result = $this->service->find($id);

        if (!$result) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success(['result' => new DocumentFullResource($result)], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $document
     * @return DocumentFullResource
     */
    public function update(UpdateRequest $request, MainModel $document)
    {
        try {
            $result = $this->service->update($request->validated(), $document);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new DocumentFullResource($result),
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MainModel  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainModel $document)
    {
        try {
            $this->service->delete($document);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success(null, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Array  $ids
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(BulkDestroyRequest $request)
    {
        try {
            $documents = $this->service->findMany($request->validated()['ids']);

            foreach($documents as $document) {
                $this->service->delete($document);
            }
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success(null, 204);
    }

    /**
     * Revert the specified resource from storage.
     *
     * @param  MainModel  $document
     * @return \Illuminate\Http\Response
     */
    public function revert(RevertRequest $request)
    {
        try {
            $id = $request->validated()['result']['id'];
            $document = $this->service->find($id);
            $this->service->delete($document);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success(null, 204);
    }

    public function download(Request $request)
    {
        $result = $this->service->download($request->get('id'));

        return $this->success([
            'result' => $result,
            'message' => Lang::get('success.downloaded')
        ], Response::HTTP_OK);
    }

    /**
     * Display audit log results of the given query.
     *
     * @return Resource
     */
    public function documentAuditLogs(int $id)
    {
        $result = $this->service->find($id);

        if (!$result) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success([
            'results' => AuditLogBasicResource::collection($result->auditLogs()->orderBy('updated_at', 'desc')->get())
        ], Response::HTTP_OK);
    }

    const BUFFER = 10;  // 10 characters: to show 10 neighbouring characters around the searched word

    /** A helper function to generate the model namespace
     * @return string
     */
    private function modelNamespacePrefix()
    {
        return app()->getNamespace() . 'Models\\';
    }

    public function test(Request $request)
    {
        $keyword = $request->search;

        // just for demonstration, you can include models that you want to exclude from the searches here
        // $toExclude = [Comment::class];
        $toExclude = [];

        // getting all the model files from the model folder
        $files = File::allFiles(app()->basePath() . '/app/Models');

        // to get all the model classes
        $results = collect($files)->map(function (SplFileInfo $file){
            $filename = $file->getRelativePathname();

            // assume model name is equal to file name
            /* making sure it is a php file*/
            if (substr($filename, -4) !== '.php'){
                return null;
            }
            // removing .php
            return substr($filename, 0, -4);

        })
        ->filter(function (?string $classname) use($toExclude){
            if($classname === null){
                return false;
            }

            // using reflection class to obtain class info dynamically
            $reflection = new \ReflectionClass($this->modelNamespacePrefix() . $classname);

            // making sure the class extended eloquent base model
            $isModel = $reflection->isSubclassOf(BaseModel::class);

            // making sure the model implemented the searchable trait
            $searchable = $reflection->hasMethod('search');

            // filter model that has the searchable trait and not in exclude array
            return $isModel && $searchable && !in_array($reflection->getName(), $toExclude, true);

        })
        ->map(function ($classname) use ($keyword) {
            // for each class, call the search function
            $model = app($this->modelNamespacePrefix() . $classname);

            // Our goal here: to add these 3 attributes to each of our search result:
            // a. `match` -- the match found in our model records
            // b. `model` -- the related model name
            // c. `view_link` -- the URL for the user to navigate in the frontend to view the resource
            return $model::search($keyword)->take(10)->get()->map(function ($modelRecord) use ($model, $keyword, $classname){
                // to create the `match` attribute, we need to join the value of all the searchable fields in
                // our model, ie all the fields defined in our 'toSearchableArray' model method
                //
                // We make use of the SEARCHABLE_FIELDS constant in our model
                // we dont want id in the match, so we filter it out.
                $fields = array_filter($model::SEARCHABLE_FIELDS, fn($field) => $field !== 'id');

                // only extracting the relevant fields from our model
                $fieldsData = $modelRecord->only($fields);

                $fieldsData = $this->populateFieldsData($fieldsData);

                // joining the fields together
                $serializedValues = collect($fieldsData)->join(' ');

                // finding the position of match
                $searchPos = strpos(strtolower($serializedValues), strtolower($keyword));

                // Our goal here:
                // After finding the match position, we also want to include the surrounding text, so our user would
                // have a better search experience.
                //
                // We append or prepend `...` if there are more text before / after our match + neighbouring text
                // including the found terms
                if($searchPos !== false){

                    // the buffer number dictates how many neighbouring characters to display
                    $start = $searchPos - self::BUFFER;
                    
                    // we don't want to go below 0 as the starting position
                    $start = $start < 0 ? 0 : $start;
                    
                    // multiply 2 buffer to cover the text before and after the match
                    $length = strlen($keyword) + 2 * self::BUFFER;

                    // getting the match and neighbouring text
                    $sliced = substr($serializedValues, $start, $length);
                    
                    // adding prefix and postfix dots
                    
                    // if start position is negative, there is no need to prepend `...`
                    $shouldAddPrefix = $start > 0;
                    // if end position went over the total length, there is no need to append `...`
                    $shouldAddPostfix = ($start + $length) < strlen($serializedValues) ;

                    $sliced =  $shouldAddPrefix ? '...' . $sliced : $sliced;
                    $sliced = $shouldAddPostfix ? $sliced . '...' : $sliced;
                }

                // use $slice as the match, otherwise if undefined we use the first 20 character of serialisedValues  
                $modelRecord->setAttribute('match', $sliced ?? substr($serializedValues, 0, 20) . '...');
                // setting the model name
                $modelRecord->setAttribute('model', $classname);
                // setting the resource link
                $modelRecord->setAttribute('view_link', $this->resolveModelViewLink($modelRecord));

                return $modelRecord;

            });
        })->flatten(1);

        return DocumentAutocompleteResource::collection($results);

        // return $this->success($results->toJson(), Response::HTTP_OK);
    }

    /** Helper function to retrieve resource URL
     * @param Model $model
     * @return string|string[]
     */
    private function resolveModelViewLink(BaseModel $model)
    {
        // Here we list down all the alternative model-link mappings
        // if we dont have a record here, will default to /{model-name}/{model_id} 
        $mapping = [
            \App\Models\Document::class => '/documents/edit/{id}'
        ];

        // getting the Fully Qualified Class Name of model
        $modelClass = get_class($model);

        // converting model name to kebab case
        $modelName = Str::plural(Arr::last(explode('\\', $modelClass)));
        $modelName = Str::kebab(Str::camel($modelName));
        
        // attempt to get from $mapping. We assume every entry has an `{id}` for us to replace
        if(Arr::has($mapping, $modelClass)){
            return str_replace('{id}', $model->id, $mapping[$modelClass]);
        }
        // assume /{model-name}/{model_id}
        return URL::to('/' . strtolower($modelName) . '/' . $model->id);

    }

    private function populateFieldsData($fieldsData) 
    {
        $data = [];

        foreach ($fieldsData as $field) {
            if (is_array($field)) {
                foreach ($field as $column) {
                    if (!is_null($column)) {
                        $data[] = $column;
                    }
                }
            } else {
                $data[] = $field;
            }
        }

        return $data;
    }
}
