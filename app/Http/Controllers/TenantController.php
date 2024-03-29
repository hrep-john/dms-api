<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\Tenant as MainModel;
use App\Http\Resources\TenantResource as BasicResource;
use App\Http\Services\Contracts\TenantServiceInterface;
use App\Http\Requests\Tenant\StoreRequest;
use App\Http\Requests\Tenant\UpdateRequest;
use App\Http\Resources\TenantListResource as ListResource;
use App\Traits\ApiResponder;
use Exception;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    use ApiResponder;

    protected $service;

    public function __construct(TenantServiceInterface $service)
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
            $this->throwError(Lang::get('error.save.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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
     * @param  MainModel  $tenant
     * @return BasicResource
     */
    public function update(UpdateRequest $request, MainModel $tenant)
    {
        try {
            $result = $this->service->update($request->validated(), $tenant);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
        }

        return $this->success([
            'result' => new BasicResource($result),
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MainModel  $tenant
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainModel $tenant)
    {
        try {
            $this->service->delete($tenant);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
        }

        return $this->success(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Display all listing of the resource.
     *
     * @return ListResource
     */
    public function list()
    {
        $results = $this->service->all();

        return $this->success([
            'results' => ListResource::collection($results)
        ], Response::HTTP_OK);
    }
}
