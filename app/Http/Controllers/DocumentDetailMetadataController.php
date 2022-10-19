<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Http\Resources\DocumentDetailMetadataResource as BasicResource;
use App\Http\Services\Contracts\DocumentDetailMetadataServiceInterface;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class DocumentDetailMetadataController extends Controller
{
    protected $service;

    public function __construct(DocumentDetailMetadataServiceInterface $service)
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

    public function extract(int $id)
    {
        $document = $this->service->find($id);

        if (is_null($document)) {
            $this->throwError(Lang::get('error.document.not.found'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        $delaySeconds = 2;
        $this->service->extract($document, auth()->user(), $delaySeconds);

        return $this->success([
            'message' => Lang::get('success.extracted')
        ], Response::HTTP_OK);
    }
}
