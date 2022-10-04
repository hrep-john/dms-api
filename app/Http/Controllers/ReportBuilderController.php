<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\ReportBuilder as MainModel;
use App\Http\Resources\ReportBuilderBasicResource as BasicResource;
use App\Http\Resources\ReportBuilderFullResource as FullResource;
use App\Http\Services\Contracts\ReportBuilderServiceInterface;
use App\Http\Requests\ReportBuilder\StoreRequest;
use App\Http\Requests\ReportBuilder\UpdateRequest;
use App\Traits\ApiResponder;
use Exception;
use Illuminate\Http\Request;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class ReportBuilderController extends Controller
{
    use ApiResponder;

    protected $service;

    public function __construct(ReportBuilderServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return FullResource
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
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return FullResource
     */
    public function store(StoreRequest $request)
    {
        try {
            $result = $this->service->store($request->validated());
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.save.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new FullResource($result),
            'message' => Lang::get('success.created')
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return FullResource
     */
    public function show(MainModel $reportBuilder)
    {
        if (!$reportBuilder) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success(['result' => new FullResource($reportBuilder)], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $reportBuilder
     * @return FullResource
     */
    public function update(UpdateRequest $request, MainModel $reportBuilder)
    {
        try {
            $result = $this->service->update($request->validated(), $reportBuilder);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), json_decode($e), Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new FullResource($result),
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MainModel  $reportBuilder
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainModel $reportBuilder)
    {
        try {
            $this->service->delete($reportBuilder);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success(null, Response::HTTP_NO_CONTENT);
    }

    public function uploadFiles(Request $request, MainModel $reportBuilder)
    {
        try {
            $file = $request->file('upload');
            $result = $this->service->uploadFiles($reportBuilder, $file);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.upload.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return response()->json([ 'fileName' => 'your file name put here', 'uploaded' => true, 'url' => $result, ]);
    }
}
