<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\Transmittal;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Transmittal\StoreRequest;
use Symfony\Component\HttpFoundation\Response;

class TransmittalController extends Controller
{
    use ApiResponder;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $id)
    {
        $transmittal = Transmittal::where('document_id', $id)->first();

        return $this->success(['transmittal' => $transmittal], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        //
    }
}
