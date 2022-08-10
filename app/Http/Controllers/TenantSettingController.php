<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\TenantSetting as MainModel;
use App\Http\Resources\TenantSettingResource as MainResource;
use App\Http\Services\Contracts\TenantSettingServiceInterface;
use App\Http\Requests\TenantSetting\StoreRequest;
use App\Http\Requests\TenantSetting\UpdateRequest;
use App\Http\Requests\TenantSettingDomainRequest;
use App\Traits\ApiResponder;
use Exception;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class TenantSettingController extends Controller
{
    use ApiResponder;

    protected $service;

    public function __construct(TenantSettingServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return MainResource
     */
    public function index()
    {
        $results = $this->service->paginate();
        $results->data = MainResource::collection($results);

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
            'result' => new MainResource($result),
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

        return $this->success(['result' => new MainResource($result)], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $domain
     * @return MainResource
     */
    public function getTenantSettingsByDomain(TenantSettingDomainRequest $request)
    {
        $results = $this->service->findByDomain($request->validated()['domain']);

        if ($results->count() === 0) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success(['results' => MainResource::collection($results)], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $tenantSetting
     * @return MainResource
     */
    public function update(UpdateRequest $request, MainModel $tenantSetting)
    {
        try {
            $result = $this->service->update($request->validated(), $tenantSetting);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'result' => new MainResource($result),
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MainModel  $tenantSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainModel $tenantSetting)
    {
        try {
            $this->service->delete($tenantSetting);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success(null, Response::HTTP_NO_CONTENT);
    }
}
