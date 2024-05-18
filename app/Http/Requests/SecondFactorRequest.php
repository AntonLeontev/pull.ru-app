<?php

namespace App\Http\Requests;

use App\Models\AuthCode;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use MoonShine\Http\Requests\LoginFormRequest as MoonshineLoginFormRequest;
use MoonShine\MoonShineAuth;

class SecondFactorRequest extends MoonshineLoginFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return MoonShineAuth::guard()->guest();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required'],
            'password' => ['required'],
            'code' => ['required'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $code = AuthCode::query()
            ->where('code', $this->get('code'))
            ->where('username', $this->get('username'))
            ->first();

        if (! $code) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'code' => __('moonshine::auth.failed'),
            ]);
        }

        $credentials = [
            config('moonshine.auth.fields.username', 'email') => $this->get(
                'username'
            ),
            config('moonshine.auth.fields.password', 'password') => $this->get(
                'password'
            ),
        ];

        if (! MoonShineAuth::guard()->attempt(
            $credentials,
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'code' => __('moonshine::auth.failed'),
            ]);
        }

        session()->regenerate();

        RateLimiter::clear($this->throttleKey());
    }
}
