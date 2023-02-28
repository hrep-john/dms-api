<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\Signatory;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Signatory\StoreRequest;
use App\Http\Requests\Signatory\UpdateRequest;
use Symfony\Component\HttpFoundation\Response;

class SignatoriesController extends Controller
{
    use ApiResponder;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        $signatories = Signatory::where('tenant_id', $user->user_info->tenant_id)->get();

        return $this->success(['signatories' => $signatories], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $user = Auth::user();

        $checkSignatory = Signatory::where('name', $request['name'])
            ->where('tenant_id', $user->user_info->tenant_id)
            ->get()->first();

        if ($checkSignatory) $this->throwError('Signatory already exists.', null, Response::HTTP_NOT_FOUND, ApiErrorResponse::VALIDATION_ERROR_CODE);

        $signatory = Signatory::create([
            'tenant_id' => $user->user_info->tenant_id,
            'name' => $request['name'],
            'designation' => $request['designation'],
            'office' => $request['office'],
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        return $this->success(['signatory' => $signatory], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Signatory  $signatory
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $signatory = Signatory::find($id);

        if (!$signatory) $this->throwError('Signatory not found.', null, Response::HTTP_NOT_FOUND, ApiErrorResponse::RESOURCE_NOT_FOUND_CODE);

        return $this->success(['signatory' => $signatory], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Signatory  $signatory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request)
    {
        $signatory = Signatory::find($request['id']);

        if (!$signatory) $this->throwError('Signatory not found.', null, Response::HTTP_NOT_FOUND, ApiErrorResponse::RESOURCE_NOT_FOUND_CODE);

        $user = Auth::user();

        $signatory->update([
            'name' => $request['name'],
            'designation' => $request['designation'],
            'office' => $request['office'],
            'updated_by' => $user->id
        ]);

        return $this->success(['signatory' => $signatory], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Signatory  $signatory
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $signatory = Signatory::find($id);

        if (!$signatory) $this->throwError('Signatory not found.', null, Response::HTTP_NOT_FOUND, ApiErrorResponse::RESOURCE_NOT_FOUND_CODE);

        $signatory->delete();

        return $this->success(['signatory' => $signatory], Response::HTTP_CREATED);
    }
}