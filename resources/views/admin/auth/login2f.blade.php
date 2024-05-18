@extends("moonshine::layouts.login")

@section('content')
    <div class="authentication" xmlns:x-slot="http://www.w3.org/1999/html">
        <div class="authentication-logo">
            <a href="/" rel="home">
                <img class="h-16"
                     src="{{ config('moonshine.logo') ?: asset('vendor/moonshine/logo.svg') }}"
                     alt="{{ config('moonshine.title') }}"
                >
            </a>
        </div>
        <div class="authentication-content">
            <div class="authentication-header">
                <p class="description">
                    Введите код из сообщения
                </p>
            </div>

            <x-moonshine::form
                class="authentication-form"
                action="{{ route('moonshine.authenticate2f') }}"
                method="POST"
                :errors="false"
            >
			<input type="hidden" name="username" value="{{ $username }}">
			<input type="hidden" name="password" value="{{ $password }}">
			<input type="hidden" name="remember" value="{{ $remember }}">
                <div class="form-flex-col">
                    <x-moonshine::form.input-wrapper
                        name="code"
                        label="Код из сообщения"
                        required
                    >
                        <x-moonshine::form.input
                            id="username"
                            type="username"
                            name="code"
                            @class(['form-invalid' => $errors->has('code')])
                            placeholder="Код"
                            required
                            autofocus
                            value="{{ old('code') }}"
                            autocomplete="off"
                        />
                    </x-moonshine::form.input-wrapper>

                </div>

                <x-slot:button type="submit" class="w-full btn-lg">
                    Отправить
                </x-slot:button>
            </x-moonshine::form>

            <p class="text-center text-2xs">
                {!! config('moonshine.auth.footer', '') !!}
            </p>

            <div class="authentication-footer">
                @include('moonshine::ui.social-auth', [
                    'title' => trans('moonshine::ui.login.or_socials')
                ])
            </div>
        </div>
    </div>
@endsection
