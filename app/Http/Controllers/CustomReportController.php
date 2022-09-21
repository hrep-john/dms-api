<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\ReportBuilder as MainModel;
use App\Http\Resources\ReportBuilderResource as BasicResource;
use App\Http\Services\Contracts\CustomReportServiceInterface;
use App\Http\Requests\CustomReport\ShowRequest;
use App\Traits\ApiResponder;
use Exception;
use Lang;
use Symfony\Component\HttpFoundation\Response;

class CustomReportController extends Controller
{
    use ApiResponder;

    protected $service;

    public function __construct(CustomReportServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  MainModel  $reportBuilder
     * @return BasicResource
     */
    public function report(ShowRequest $request, $slug)
    {
        try {
            $attributes = $request->validated();
            $attributes['slug'] = $slug;
            $result = $this->service->report($attributes);
        } catch (Exception $e) {
            Logger($e);
            $this->throwError(Lang::get('error.update.failed'), json_decode($e), Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            // 'result' => new BasicResource($result),
            'result' => $result,
            'message' => Lang::get('success.updated')
        ], Response::HTTP_OK);
    }
}
