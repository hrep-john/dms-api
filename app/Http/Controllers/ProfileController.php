<?php

namespace App\Http\Controllers;

use App\Helpers\ApiErrorResponse;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UpdateProfile;
use App\Http\Requests\UploadProfilePicture;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Storage;
use Str;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    use ApiResponder;

    /**
     * Show current auth user profile
     *
     * @return Response
     */
    public function show()
    {
        $user = Auth::user();
        return $this->success([
            'user' => $user->flattenUserInfo(), 
            'roles' => $user->user_roles,
            'permissions' => $user->user_permissions,
        ], Response::HTTP_OK);
    }

    /**
     * Update current auth user profile
     * @param UpdateProfile $request
     * @return Response
     */
    public function update(UpdateProfile $request)
    {
        $user = Auth::user();
        $user->update($request->only('email'));
        $user->userInfo()->update($request->except('email'));

        // Re-assignment to run eager loading of user_info
        $user = User::find($user->id);

        return $this->success(['user' => $user->flattenUserInfo()], Response::HTTP_OK);
    }

    /**
     * Update current user's password
     * @param UpdatePassword $request
     * @return Response
     */
    public function changePassword(UpdatePassword $request)
    {
        $user = Auth::user();
        $request['password'] = Hash::make($request['password']);
        $user->update(['password' => $request['password']]);

        return $this->success(NULL, Response::HTTP_NO_CONTENT);
    }

    /**
     * Upload current profile picture
     * @param UploadProfilePicture $request
     * @return Response
     */
    public function uploadProfilePicture(UploadProfilePicture $request)
    {
        $fileName = time() . '_' . $request->file('image')->getClientOriginalName();

        // Check the filename length
        if (strlen($fileName) > 150) {
            $this->throwError('The uploaded filename is too long.', ['image' => ['The filename is too long.']], Response::HTTP_UNPROCESSABLE_ENTITY, ApiErrorResponse::VALIDATION_ERROR_CODE);
        }

        try {
            $objectKey = Storage::disk('s3')->putFileAs('profile-pictures', $request->file('image'), $fileName);
            $url = Storage::disk('s3')->url($objectKey);
        } catch (\Throwable $th) {
            throw $th;
        }

        $start = 0;
        $end = strpos($url, '/profile-pictures');

        $s3Domain = Str::substr($url, $start, $end);
        $url = Str::replace($s3Domain, env('AWS_STORAGE_URL', $s3Domain), $url);

        return $this->success(['url' => $url], Response::HTTP_OK);
    }

}
