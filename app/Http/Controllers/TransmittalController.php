<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\Transmittal;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Transmittal\StoreRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

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
    public function upload(StoreRequest $request)
    {
        $s3Path = null;

        try {
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName = $request->document_id . '.' . $extension;
            $s3Path = 'transmittals/' . $fileName;
            Storage::disk('s3')->putFileAs('transmittals', $request->file('image'), $fileName);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $this->throwError("We've encountered an error in uploading your file", $error, 502, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        $url = 'https://s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $fullPath = $url . $s3Path;

        $user = Auth::user();

        $transmittal = Transmittal::where('document_id', $request->id)->first();
        if ($transmittal) {
            $transmittal->update([
                'transmittal_url' => $fullPath,
                'updated_by' => $user->id
            ]);
        } else {
            Transmittal::create([
                'document_id' => $request->document_idd,
                'transmittal_url' => $fullPath,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);
        }

        return $this->success(['file_path' => $fullPath], Response::HTTP_OK);
    }
}
