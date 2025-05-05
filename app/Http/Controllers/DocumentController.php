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
use App\Http\Resources\DocumentFullResource;
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
            $this->throwError(Lang::get('error.save.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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

        $this->service->writeDocumentAuditLog($result, 'viewed');

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
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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

    public function preview(Request $request)
    {
        $result = $this->service->preview($request->get('id'));

        return $this->success([
            'result' => $result,
            'message' => Lang::get('success.viewed')
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
            'results' => AuditLogBasicResource::collection($result->auditLogs()->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->get())
        ], Response::HTTP_OK);
    }

    public function setDocumentSearchable(int $id)
    {
        $document = MainModel::find($id);

        if (!$document) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        $document->searchable();

        return $this->success(null, 204);
    }
}
