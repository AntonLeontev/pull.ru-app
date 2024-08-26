<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRegisterForCashierRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'surname' => ['required', 'string', 'max:50'],
            'birthday' => ['required', 'date', 'max:50'],
            'phone' => ['required', 'string', 'size:12', 'starts_with:+79', 'unique:clients,phone'],
            'email' => ['required', 'email', 'max:50', 'unique:clients,email'],
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

    protected function prepareForValidation()
    {
        $this->merge(['phone' => normalize_phone($this->get('phone'))]);
    }
}
