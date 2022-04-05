<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth\Requests\AuthRequest;
use Auth\Services\EmailLoginService;
use Auth\Services\EmailRegisterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    /**
     * Sign in the account.
     *
     * @param Request           $request
     * @param EmailLoginService $service
     *
     * @return Response
     */
    public function login(Request $request, EmailLoginService $service): Response
    {
        try {
            return $service->execute($request->email, $request->password);
        } catch (Throwable $th) {
            Log::error($th->getMessage());

            throw $th;
        }
    }
}
