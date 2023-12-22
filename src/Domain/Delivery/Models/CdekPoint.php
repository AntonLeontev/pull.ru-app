<?php

namespace Src\Domain\Delivery\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CdekPoint extends Model
{
    use HasFactory;

    const CREATED_AT = null;

    protected $fillable = [
        'code',
        'name',
        'uuid',
        'work_time',
        'type',
        'owner_code',
        'take_only',
        'is_handout',
        'is_reception',
        'is_dressing_room',
        'is_ltl',
        'have_cashless',
        'have_cash',
        'allowed_cod',
        'weight_min',
        'weight_max',
        'country_code',
        'region_code',
        'region',
        'city_code',
        'city',
        'fias_guid',
        'postal_code',
        'longitude',
        'latitude',
        'address',
        'address_full',
        'fulfillment',
    ];

    protected $casts = [
        'take_only' => 'boolean',
        'is_handout' => 'boolean',
        'is_reception' => 'boolean',
        'is_dressing_room' => 'boolean',
        'is_ltl' => 'boolean',
        'have_cashless' => 'boolean',
        'have_cash' => 'boolean',
        'allowed_cod' => 'boolean',
        'fulfillment' => 'boolean',
    ];

    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'uuid' => $this->uuid,
            'work_time' => $this->work_time,
            'type' => $this->type,
            'owner_code' => $this->owner_code,
            'take_only' => $this->take_only,
            'is_handout' => $this->is_handout,
            'is_reception' => $this->is_reception,
            'is_dressing_room' => $this->is_dressing_room,
            'is_ltl' => $this->is_ltl,
            'have_cashless' => $this->have_cashless,
            'have_cash' => $this->have_cash,
            'allowed_cod' => $this->allowed_cod,
            'weight_min' => $this->weight_min,
            'weight_max' => $this->weight_max,
            'location' => [
                'country_code' => $this->country_code,
                'region_code' => $this->region_code,
                'region' => $this->region,
                'city_code' => $this->city_code,
                'city' => $this->city,
                'fias_guid' => $this->fias_guid,
                'postal_code' => $this->postal_code,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'address' => $this->address,
                'address_full' => $this->address_full,
            ],
            'fulfillment' => $this->fulfillment,
        ];
    }
}
