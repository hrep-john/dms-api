<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\ReportBuilder as MainModel;
use App\Http\Resources\ReportBuilderResource as BasicResource;
use App\Http\Services\Contracts\ReportBuilderServiceInterface;
use App\Http\Requests\ReportBuilder\StoreRequest;
use App\Http\Requests\ReportBuilder\UpdateRequest;
use App\Traits\ApiResponder;
use Exception;
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
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return BasicResource
     */
    public function store(StoreRequest $request)
    {
        try {
            $result = $this->service->store($request->validated());
        } catch (Exception $e) {
            Logger($e);
            $this->throwError(Lang::get('error.save.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new BasicResource($result),
            'message' => Lang::get('success.created')
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return BasicResource
     */
    public function show(MainModel $reportBuilder)
    {
        if (!$reportBuilder) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success(['result' => new BasicResource($reportBuilder)], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $reportBuilder
     * @return BasicResource
     */
    public function update(UpdateRequest $request, MainModel $reportBuilder)
    {
        try {
            $result = $this->service->update($request->validated(), $reportBuilder);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), json_decode($e), Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new BasicResource($result),
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
}
