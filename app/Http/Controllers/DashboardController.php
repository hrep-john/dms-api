<?php

namespace App\Http\Controllers;

use App;
use Lang;
use Exception;
use ApiErrorResponse;
use App\Http\Resources\DocumentDashboardListResource;
use App\Http\Services\Contracts\DocumentServiceInterface;
use App\Models\Document;
use App\Traits\ApiResponder;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    use ApiResponder;

    /**
     * Display a listing of the resource.
     *
     * @return BasicResource
     */
    public function index()
    {
        $recentDocuments = App::make(DocumentServiceInterface::class)->paginate();
        $assignedDocuments = App::make(DocumentServiceInterface::class)->recentlyAssignedDocuments();
        // Logger($assignedDocuments->toSql());
        Logger($assignedDocuments);

        return $this->success([
            // 'total_documents' => App::make(DocumentServiceInterface::class)->totalCount(),
            'recent_documents' => DocumentDashboardListResource::collection($recentDocuments),
            'assigned_documents' => DocumentDashboardListResource::collection($assignedDocuments),
        ], Response::HTTP_OK);
    }
}
