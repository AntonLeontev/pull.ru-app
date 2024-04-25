<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RightholdersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appeal' => ['required', 'string', 'max:150'],
            'product_links' => ['array'],
            'product_links.*' => ['url'],
            'product_links_file' => ['file', 'max:6000', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'who' => ['required', 'string', 'max:150'],
            'brand' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'company' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'text' => ['required', 'file', 'max:6000', 'mimetypes:application/pdf'],
            'confirm' => ['required', 'file', 'max:6000', 'mimetypes:application/pdf'],
            'powers' => ['required', 'file', 'max:6000', 'mimetypes:application/pdf'],
            'rights' => ['required', 'file', 'max:6000', 'mimetypes:application/pdf'],
        ];
    }

    // public function messages()
    // {

    // }

    public function attributes()
    {
        return [
            'appeal' => 'Суть жалобы',
            'product_links' => 'Ссылки на товары',
            'product_links.*' => 'Ссылкa',
            'product_links_file' => 'Файл со ссылками на товары',
            'who' => 'Кто Вы',
            'brand' => 'Название бренда',
            'name' => 'ФИО',
            'company' => 'Название компании',
            'phone' => 'Телефон',
            'email' => 'Email',
            'text' => 'Текст жалобы',
            'confirm' => 'Подтверждение нарушения права',
            'powers' => 'Полномочия',
            'rights' => 'Подтверждение Вашего права',
        ];
    }
}
