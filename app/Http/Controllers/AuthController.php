<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Helpers\ApiErrorResponse;
use App\Http\Requests\LoginUser;
use App\Http\Requests\ResetPassword;
use App\Http\Requests\ShowEmailAvailability;
use App\Http\Requests\StoreForgotPassword;
use App\Http\Requests\StoreUser;
use App\Mail\PasswordReset;
use App\Mail\PasswordResetOtp;
use App\Models\User;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lang;
use Symfony\Component\HttpFoundation\Response;
use MeiliSearch;

class AuthController extends Controller
{
    use ApiResponder;

    /**
     * Register a new user
     * @param StoreUser $request
     * @return Response
     */
    public function register(StoreUser $request)
    {
        $request['password'] = Hash::make($request['password']);
        $newUser = User::create($request->only('email', 'password'));
        $newUser->userInfo()->create($request->except('email', 'password', 'password_confirmation'));

        // Re-assignment to run eager loading of user_info
        $newUser = User::find($newUser->id);

        Auth::attempt(['email' => $request['email'], 'password' => $request['password']]);

        $token = $newUser->createToken('api_token')->plainTextToken;
        $type = 'Bearer';

        // Assign encoder user permissions
        $newUser->assignRole('encoder');
        $rolesNames = $newUser->getRoleNames();

        return $this->success([
            'user' => $newUser->flattenUserInfo(), 
            'roles' => $rolesNames, 
            'access_token' => $token, 
            'token_type' => $type
        ], Response::HTTP_CREATED);
    }

    /**
     * Login to an existing user
     * @param LoginUser $request
     * @return Response
     */
    public function login(LoginUser $request)
    {
        if (!$this->validateLoginCredentials($request->all())) {
            $this->throwError(Lang::get('validation.invalid.user.id.password'), NULL, Response::HTTP_UNAUTHORIZED, ApiErrorResponse::INVALID_CREDENTIALS_CODE);
        }

        $token = Auth::user()->createToken('api_token')->plainTextToken;
        $type = 'Bearer';

        // Get roles
        $roles = Auth::user()->getRoleNames();

        return $this->success([
            'user' => Auth::user()->flattenUserInfo(), 
            'roles' => $roles, 
            'access_token' => $token, 
            'token_type' => $type
        ], Response::HTTP_OK);
    }

    /**
     * Revoke the current access token of the auth user
     *
     * @return Response
     */
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->success(NULL, Response::HTTP_NO_CONTENT);
    }

    /**
     * Check if the email address is available for assignment
     * @param ShowEmailAvailability $request
     * @return Response
     */
    public function showEmailAvailability(ShowEmailAvailability $request)
    {
        $emailExists = User::where('email', '=', $request['email'])->first();

        return $this->success([
            'email_available' => !$emailExists
        ], Response::HTTP_OK);
    }

    /**
     * Create a password reset request and send an email (SPA)
     * @param StoreForgotPassword $request
     * @return Response
     */
    public function forgotPassword(StoreForgotPassword $request)
    {
        $user = User::where('email', '=', $request['email'])->first();
        $type = $request['type'];

        if ($this->verifyIfUserExistingTokenStillValid($user)) {
            return false; 
        }

        if ($type === 'spa' || !$type) $token = $this->generateToken();
        else $token = $this->generateToken('mobile');

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $tenantDomain = $user->userInfo->tenant->domain ?? env('SPA_RESET_PASSWORD_URL');
        $tenantDomain = Str::startsWith($tenantDomain, ['https://', 'http://']) ? $tenantDomain : sprintf('%s%s', 'http://', $tenantDomain);
        $reset_pass_link = sprintf('%s/auth/reset-password?token=%s&username=%s', $tenantDomain, $token, $user->username);

        try {
            if ($type === 'spa' || !$type) Mail::to($user->email)->send(new PasswordReset($reset_pass_link));
            else Mail::to($user->email)->send(new PasswordResetOtp($token));
        } catch (\Exception $_e) {
            $this->throwError(Lang::get('error.email.failed'), null, Response::HTTP_BAD_GATEWAY, ApiErrorResponse::SMTP_ERROR_CODE);
        }

        return $this->success(['message' => Lang::get('info.reset.password.sent', [
            'email' => $user['email']
        ])], Response::HTTP_OK);
    }

    protected function verifyIfUserExistingTokenStillValid($user)
    {
        $resetRequest = DB::table('password_resets')
            ->where('email', $user->email)
            ->whereBetween('created_at', [now()->subSeconds(58), now()])
            ->first();

        if ($resetRequest) {
            $createdAtAfterMinute = Carbon::parse($resetRequest->created_at)->addMinute();
            $errorDescription = Lang::get('info.reset.password.try.again', [
                'email' => $user->email, 
                'seconds' => $createdAtAfterMinute->diffInSeconds(now())
            ]);

            $this->throwError($errorDescription, NULL, Response::HTTP_UNPROCESSABLE_ENTITY, ApiErrorResponse::VALIDATION_ERROR_CODE);
            return true;
        }


        return false;
    }

    /**
     * Reset password via forgot password token
     * @param ResetPassword $request
     * @return Response
     */
    public function resetPassword(ResetPassword $request)
    {
        $user = User::where('username', $request['username'])->first();

        $resetRequest = DB::table('password_resets')
            ->where('email', $user->email)
            ->where('token', $request['token'])
            ->first();

        if (!$resetRequest) {
            $this->throwError(Lang::get('error.incorrect.email.reset.password.token'), NULL, Response::HTTP_UNPROCESSABLE_ENTITY, ApiErrorResponse::VALIDATION_ERROR_CODE);
        }

        // Change current password
        $user->password = Hash::make($request['password']);
        $user->save();

        // Remove password reset request from the table
        DB::table('password_resets')->where('email', $user->email)->delete();

        return $this->success([
            'message' => Lang::get('success.password.reset')
        ], Response::HTTP_OK);
    }

    /**
     * Generate forgot password token
     * @return string
     */
    private function generateToken(string $type = NULL)
    {
        if (!$type || $type === 'spa') {
            $key = config('app.key');

            if (Str::startsWith($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return hash_hmac('sha256', Str::random(40), $key);

        } else {
            return Str::upper(Str::random(6));
        }
    }

    private function validateLoginCredentials($credentials) 
    {
        $flag = false;

        if (Auth::attempt($credentials)) {
            $user = auth()->user();
            $tenantDomain = \App\Helpers\tenantDomain();

            if (!$user || in_array(UserRole::Superadmin, $user->user_roles->toArray())) {
                $flag = true;
            }

            if (Str::lower(Auth::user()->userInfo->tenant->domain) === Str::lower($tenantDomain)) {
                $flag = true;
            }
        }

        return $flag;
    }
}
