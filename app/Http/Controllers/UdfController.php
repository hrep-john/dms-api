<?php

namespace App\Http\Controllers;

use Lang;
use Exception;
use ApiErrorResponse;
use App\Http\Services\Contracts\UserDefinedFieldServiceInterface;
use App\Traits\ApiResponder;
use App\Http\Resources\UserDefinedFieldResource as BasicResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Udf\StoreRequest;
use App\Http\Requests\Udf\UpdateRequest;
use App\Models\UserDefinedField as MainModel;

class UdfController extends Controller
{
    use ApiResponder;

    protected $service;

    public function __construct(UserDefinedFieldServiceInterface $service)
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
     * @return MainResource
     */
    public function store(StoreRequest $request)
    {
        try {
            $result = $this->service->store($request->validated());
        } catch (Exception $e) {
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
     * @return MainResource
     */
    public function show(int $id)
    {
        $result = $this->service->find($id);

        if (!$result) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success(['result' => new BasicResource($result)], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $udf
     * @return MainResource
     */
    public function update(UpdateRequest $request, MainModel $udf)
    {
        try {
            $result = $this->service->update($request->validated(), $udf);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new BasicResource($result),
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MainModel  $udf
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainModel $udf)
    {
        try {
            $this->service->delete($udf);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Display all listing of the resource.
     *
     * @return BasicResource
     */
    public function all()
    {
        $results = $this->service->all();

        return $this->success([
            'results' => BasicResource::collection($results)
        ], Response::HTTP_OK);
    }
}
