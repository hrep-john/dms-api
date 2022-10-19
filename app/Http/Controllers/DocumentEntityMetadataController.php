<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Http\Resources\DocumentEntityMetadataResource as BasicResource;
use App\Http\Services\Contracts\DocumentEntityMetadataServiceInterface;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class DocumentEntityMetadataController extends Controller
{
    protected $service;

    public function __construct(DocumentEntityMetadataServiceInterface $service)
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
        // PAGINATION
        $results = $this->service->paginate();
        $results->data = BasicResource::collection($results);

        return $this->success([
            'results' => $this->paginate($results)
        ], Response::HTTP_OK);
    }

    public function analyze(int $id)
    {
        $document = $this->service->find($id);

        if (is_null($document)) {
            $this->throwError(Lang::get('error.document.not.found'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        $this->service->analyze($document, auth()->user());

        return $this->success([
            'message' => Lang::get('success.analyzed')
        ], Response::HTTP_OK);
    }
}
