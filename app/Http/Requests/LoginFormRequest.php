<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use MoonShine\Http\Requests\LoginFormRequest as MoonshineLoginFormRequest;
use MoonShine\MoonShineAuth;

class LoginFormRequest extends MoonshineLoginFormRequest
{
    /**
     * Attempt to authenticate the request's credentials.
     *
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            config('moonshine.auth.fields.username', 'email') => $this->get(
                'username'
            ),
            config('moonshine.auth.fields.password', 'password') => $this->get(
                'password'
            ),
        ];

        if (! MoonShineAuth::guard()->validate($credentials)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => __('moonshine::auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }
}
