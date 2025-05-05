<?php

namespace App\Http\Controllers;

use ApiErrorResponse;
use App\Models\Transmittal;
use App\Models\Document;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Transmittal\StoreRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Event;
use \OwenIt\Auditing\Events\AuditCustom;

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

        $document_id = $request->document_id;

        try {
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName = $document_id . '.' . $extension;
            $s3Path = '/transmittals/' . $fileName;
            Storage::disk('s3')->putFileAs('transmittals', $request->file('image'), $fileName);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $this->throwError("We've encountered an error in uploading your file", $error, 502, ApiErrorResponse::SERVER_ERROR_CODE);
        }

        $url = env('AWS_STORAGE_URL');
        $fullPath = $url . $s3Path;

        $user = Auth::user();

        $transmittal = Transmittal::where('document_id', $document_id)->first();
        if ($transmittal) {
            $transmittal->update([
                'transmittal_url' => $fullPath,
                'updated_by' => $user->id
            ]);

            $this->writeDocumentAuditLog($document_id, 'updated transmittal');
        } else {
            Transmittal::create([
                'document_id' => $document_id,
                'transmittal_url' => $fullPath,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);

            $this->writeDocumentAuditLog($document_id, 'uploaded transmittal');
        }

        return $this->success(['file_path' => $fullPath], Response::HTTP_OK);
    }

    public function print(int $document_id)
    {
        $this->writeDocumentAuditLog($document_id, 'printed transmittal');

        return $this->success(['transmittal' => 'printed'], Response::HTTP_OK);
    }

    public function writeDocumentAuditLog($document_id, $event, $old = [], $new = [])
    {
        $document = Document::find($document_id);

        $document->auditEvent = $event;
        $document->isCustomEvent = true;
        $document->auditCustomOld = $old;
        $document->auditCustomNew = $new;

        Event::dispatch(AuditCustom::class, [$document]);
    }
}
