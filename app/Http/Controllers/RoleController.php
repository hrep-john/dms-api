<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\Role as MainModel;
use App\Http\Resources\RoleResource as BasicResource;
use App\Http\Resources\RoleListResource as RoleListResource;
use App\Http\Resources\PermissionListResource as PermissionListResource;
use App\Http\Services\Contracts\RoleServiceInterface;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Http\Resources\RoleFullResource as FullResource;
use App\Traits\ApiResponder;
use Exception;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    use ApiResponder;

    protected $service;

    public function __construct(RoleServiceInterface $service)
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
     * @return FullResource
     */
    public function store(StoreRequest $request)
    {
        try {
            $result = $this->service->store($request->validated());
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.save.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
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
    public function show(int $id)
    {
        $result = $this->service->find($id);

        if (!$result) {
            $this->throwError(Lang::get('error.show.failed'), NULL, Response::HTTP_NOT_FOUND, ApiErrorResponse::UNKNOWN_ROUTE_CODE);
        }

        return $this->success(['result' => new FullResource($result)], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $role
     * @return FullResource
     */
    public function update(UpdateRequest $request, MainModel $role)
    {
        try {
            $result = $this->service->update($request->validated(), $role);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
        }

        return $this->success([
            'result' => new FullResource($result),
            'auth_user_permissions' => auth()->user()->user_permissions,
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MainModel  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainModel $role)
    {
        try {
            $this->service->delete($role);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.delete.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE, $e->getMessage());
        }

        return $this->success(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Display all listing of the resource.
     *
     * @return RoleListResource
     */
    public function list()
    {
        $results = $this->service->all();

        return $this->success([
            'results' => RoleListResource::collection($results)
        ], Response::HTTP_OK);
    }

    /**
     * Display all permission listing of the resource.
     *
     * @return PermissionListResource
     */
    public function permissionList()
    {
        $results = $this->service->permissionList();

        return $this->success([
            'results' => $results
        ], Response::HTTP_OK);
    }
}
