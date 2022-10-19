<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App;
use App\Models\ReportBuilder as MainModel;
use App\Http\Resources\ReportBuilderResource as BasicResource;
use App\Http\Services\Contracts\CustomReportServiceInterface;
use App\Http\Requests\CustomReport\ShowRequest;
use App\Http\Services\Contracts\ReportBuilderServiceInterface;
use App\Http\Services\Contracts\UserServiceInterface;
use App\Models\UserDefinedField;
use App\Traits\ApiResponder;
use Arr;
use Carbon\Carbon;
use Exception;
use Lang;
use Str;
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
            $results = $this->paginate($results);
            $results['data'] = $this->formatDataByColumnSettings($results['data'], $template);
        } catch (Exception $e) {
            $this->throwError(Lang::get('error.show.failed'), Arr::wrap($e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        return $this->success([
            'results' => [
                'info' => $template,
                'data' => $results['data'],
                'meta' => $results['meta'],
            ]
        ], Response::HTTP_OK);
    }

    private function formatDataByColumnSettings($data, $template)
    {
        $columnSettings = JSON_DECODE(JSON_DECODE($template->format)->column_settings);
        $rawColumns = collect($columnSettings)->where('raw', true)->toArray();
        $rawFields = Arr::pluck($rawColumns, 'field');
        $formattedData = [];

        foreach($data as $row) {
            $formattedRow = [];

            foreach ($row as $column => $value) {
                if ($value == 'null') {
                    $value = null;
                } else if (in_array($column, $rawFields)) {
                    $type = $this->getColumnType($rawColumns, $column);

                    if ($type === 'dropdown') {
                        $udf = UserDefinedField::where('key', $column)->first();
                        $udfSettings = JSON_DECODE($udf->settings);
                        $udfSource = $udfSettings->source;

                        if ($udfSource === 'custom') {
                            $udfData = $udfSettings->data;
                            $selected = collect($udfData)->where('id', $value)->first();

                            if (!is_null($selected)) {
                                $value = $selected->label;
                            }
                        } else if ($udfSource === 'users') {
                            $user = App::make(UserServiceInterface::class)->find($value);
                            $value = $user->user_info->full_name ?? '';
                        }
                    } else if ($type === 'date') {
                        if (!is_null($value)) {
                            $value = $value/1000;
                            $value = Carbon::parse($value)->format('Y-m-d');
                        }
                    }
                }

                $formattedRow[$column] = $value;
            }

            $formattedData[] = $formattedRow;
        }

        return $formattedData;
    }

    private function getColumnType($rawFields, $column)
    {
        $type = '';

        foreach ($rawFields as $rawField) {
            if ($rawField->field === $column) {
                $type = $rawField->type;
            }
        }

        return $type;
    }
}
