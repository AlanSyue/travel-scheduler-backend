<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth\Requests\AuthRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Auth\Services\EmailLoginService;
use Illuminate\Support\Facades\Hash;
use Auth\Services\EmailRegisterService;
use Laravel\Socialite\Facades\Socialite;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Create a new user.
     *
     * @param AuthRequest          $request
     * @param EmailRegisterService $service
     *
     * @return void
     */
    public function register(AuthRequest $request, EmailRegisterService $service)
    {
        $request->validated();

        $auth_dto = $request->toDto();

        try {
            $service->execute($auth_dto);
        } catch (Throwable $th) {
            Log::error($th->getMessage());

            throw $th;
        }
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        try {
            /** @var User $user */
            $user = auth('api')->user();
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Sign in the account.
     *
     * @param Request           $request
     * @param EmailLoginService $service
     *
     * @return JsonResponse
     */
    public function login(Request $request, EmailLoginService $service): JsonResponse
    {
        try {
           return response()->json($service->execute($request->email, $request->password));
        } catch (Throwable $th) {
            Log::error($th->getMessage());

            throw $th;
        }
    }

    public function appleLogin(Request $request, EmailLoginService $service)
    {
        $provider = 'apple';
        $token = $request->token;

        $social_user = Socialite::driver($provider)->userFromToken($token);
        $user = $this->getLocalUser($social_user);

        try {
            return response()->json($service->execute($user->email, $social_user->id));
        } catch (Throwable $th) {
            Log::error($th->getMessage());

            throw $th;
        }
    }

    /**
     * @param OAuthTwoUser $socialUser
     * @param mixed        $social_user
     *
     * @return null|User
     */
    private function getLocalUser($social_user): ?User
    {
        $user = User::where('email', $social_user->email)->first();

        if (! $user) {
            $user = $this->registerAppleUser($social_user);
        }

        return $user;
    }

    /**
     * @param OAuthTwoUser $socialUser
     * @param mixed        $social_user
     *
     * @return null|User
     */
    private function registerAppleUser($social_user): ?User
    {
        $user = User::create([
            'name' => $social_user->name ? $social_user->name : '新訪客',
            'email' => $social_user->email,
            'password' => Hash::make($social_user->id),
        ]);

        return $user;
    }
}
