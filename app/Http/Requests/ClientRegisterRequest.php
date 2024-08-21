<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'surname' => ['required', 'string', 'max:50'],
            'birthday' => ['required', 'date', 'max:50'],
            'phone' => ['required', 'string', 'size:18', 'starts_with:+7'],
            'email' => ['required', 'email', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'birthday' => 'День рождения',
            'phone' => 'Телефон',
            'email' => 'Email',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.size' => 'Неверный формат номера',
        ];
    }

    protected function passedValidation()
    {
        $this->merge(['phone' => '+'.preg_replace('~\D~', '', $this->get('phone'))]);
    }
}
