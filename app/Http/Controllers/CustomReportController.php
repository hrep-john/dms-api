<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App;
use App\Models\ReportBuilder as MainModel;
use App\Http\Resources\ReportBuilderResource as BasicResource;
use App\Http\Services\Contracts\CustomReportServiceInterface;
use App\Http\Requests\CustomReport\ShowRequest;
use App\Http\Services\Contracts\ReportBuilderServiceInterface;
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
    public function report(ShowRequest $request, string $slug)
    {
        try {
            $attributes = $request->validated();
            $template = App::make(ReportBuilderServiceInterface::class)->getTemplateBySlug($slug);
            $filters = $attributes['filters'] ?? [];

            $results = $this->service->report($template, $filters);
            $results->data = $results;
            $results = $this->paginate($results);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.update.failed'), NULL, Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'results' => [
                'info' => $template,
                'data' => $results['data'],
                'meta' => $results['meta'],
            ]
        ], Response::HTTP_OK);
    }
}
