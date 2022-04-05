<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth\Requests\AuthRequest;
use Auth\Services\EmailRegisterService;
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
            throw $th;
        }
    }
}
