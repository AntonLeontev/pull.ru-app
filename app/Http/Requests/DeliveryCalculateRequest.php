<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryCalculateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'localityId' => ['required', 'int'],
            'estimatedCost' => ['required'],
            'payment' => ['required'],
            'weight' => ['required'],
            'width' => ['required'],
            'height' => ['required'],
            'length' => ['required'],
        ];
    }
}
