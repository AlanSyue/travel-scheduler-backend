<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth\Requests\AuthRequest;

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

}
