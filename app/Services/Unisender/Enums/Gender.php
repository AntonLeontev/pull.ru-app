<?php

namespace App\Services\Unisender\Enums;

enum Gender: string
{
    case male = 'Мужской';
    case female = 'Женский';
    case notSet = 'Предпочитаю не указывать';

    public static function fromForm($value)
    {
        return match ($value) {
            '1' => self::male,
            '2' => self::female,
            '3' => self::notSet,
            default => self::notSet,
        };
    }
}
