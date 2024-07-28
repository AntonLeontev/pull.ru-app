<?php

namespace App\Services\Ip2location;

use Illuminate\Http\Client\Response;
use Stringable;

readonly class Ip2locationDTO implements Stringable
{
    public function __construct(
        public string $ip,
        public string $countryCode,
        public string $countryName,
        public string $regionName,
        public string $cityName,
        public float $latitude,
        public float $longitude,
        public string $zipCode,
        public string $timeZone,
        public string $asn,
        public string $as,
        public bool $isProxy,
    ) {}

    public static function fromResponse(Response $response): static
    {
        return new static(
            $response->json('ip'),
            $response->json('country_code'),
            $response->json('country_name'),
            $response->json('region_name'),
            $response->json('city_name'),
            $response->json('latitude'),
            $response->json('longitude'),
            $response->json('zip_code'),
            $response->json('time_zone'),
            $response->json('asn'),
            $response->json('as'),
            $response->json('is_proxy'),
        );
    }

    public function __toString()
    {
        return $this->cityName.', '.$this->regionName.', '.$this->countryName;
    }
}
