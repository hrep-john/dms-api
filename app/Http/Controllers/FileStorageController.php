<?php

namespace App\Http\Controllers;

use App\Helpers\ApiErrorResponse;
use App\Traits\ApiResponder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class FileStorageController extends Controller
{
    use ApiResponder;

    /**
     * @throws FileNotFoundException
     * @return Response
     */
    public function showProfilePicture()
    {
        if (!Auth::user()->userInfo->profile_picture_url) {
            throw ApiErrorResponse::createErrorResponse('User has no associated profile picture', NULL, 404, ApiErrorResponse::RESOURCE_NOT_FOUND_CODE);
        }

        $fileName = pathinfo(Auth::user()->userInfo->profile_picture_url)['basename'];

        $path = storage_path('app/public/uploads/profile_pictures/' . $fileName);

        if (!File::exists($path)) {
            $this->throwError('Cannot resolve profile picture filepath', NULL, 404, ApiErrorResponse::RESOURCE_NOT_FOUND_CODE);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        return $this->success($file, 200, ['Content-Type' => $type]);
    }
}
