<?php

namespace App\Services\Unisender;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class UnisenderApi
{
    public static function getLists(): Response
    {
        return Http::unisender()
            ->post('getLists', [
                'api_key' => config('services.unisender.key'),
                'format' => 'json',
            ]);
    }

    /**
     * Этот метод добавляет контакты (email адрес и/или мобильный телефон) контакта в один или несколько списков,
     * а также позволяет добавить/поменять значения дополнительных полей и меток.
     *
     * @see https://www.unisender.com/ru/support/api/contacts/subscribe/
     */
    public static function subscribe(
        array|string|int $listIds,
        array $fields,
        array|string $tags = [],
        int $doubleOptIn = 3,
        int $overwrite = 0,
    ): Response {
        if (is_array($listIds)) {
            $listIds = implode(',', $listIds);
        }

        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }

        return Http::unisender()
            ->post('subscribe', [
                'api_key' => config('services.unisender.key'),
                'format' => 'json',
                'list_ids' => $listIds,
                'fields' => $fields,
                'tags' => $tags,
                'double_optin' => $doubleOptIn,
                'overwrite' => $overwrite,
            ]);
    }

    public static function getContact(
        string $email,
        bool $includeFields = true,
        bool $includeLists = false,
        bool $includeDetails = false,
    ): Response {
        return Http::unisender()
            ->post('getContact', [
                'api_key' => config('services.unisender.key'),
                'email' => $email,
                'include_lists' => $includeLists,
                'include_fields' => $includeFields,
                'include_details' => $includeDetails,
            ]);
    }

    public static function getFields(): Response
    {
        return Http::unisender()
            ->post('getFields', [
                'api_key' => config('services.unisender.key'),
            ]);
    }
}
