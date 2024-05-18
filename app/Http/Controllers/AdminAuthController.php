<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginFormRequest;
use App\Http\Requests\SecondFactorRequest;
use App\Models\AuthCode;
use App\Services\Telegram\TelegramService;
use Illuminate\Validation\ValidationException;
use MoonShine\Http\Controllers\AuthenticateController;

class AdminAuthController extends AuthenticateController
{
    /**
     * @throws ValidationException
     */
    public function authenticateFirstFactor(LoginFormRequest $request)
    {
        $request->authenticate();

        $code = AuthCode::updateOrCreate(
            ['username' => $request->get('username')],
            ['code' => random_int(11111, 99999)],
        );

        TelegramService::sendMessage("$code->code - код для входа");

        return view('admin.auth.login2f', [
            'username' => $request->get('username'),
            'password' => $request->get('password'),
            'remember' => $request->get('remember'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function authenticateSecondFactor(SecondFactorRequest $request)
    {
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            return view('admin.auth.login2f', [
                'username' => $request->get('username'),
                'password' => $request->get('password'),
                'remember' => $request->get('remember'),
                'code' => $request->code,
            ]);
        }

        return redirect()
            ->intended(route(config('moonshine.route.index_route', 'moonshine.index')));
    }
}
