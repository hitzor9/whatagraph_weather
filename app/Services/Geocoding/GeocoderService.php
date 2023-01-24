<?php

declare(strict_types=1);

namespace App\Services\Geocoding;

use Illuminate\Support\Facades\Cache;

class GeocoderService
{
    public function __construct(
        private GeocoderInterface $geocoderInterface
    )
    {
    }

    public function getCityCoordinates(string $cityName): CityDto
    {
        $result = Cache::rememberForever(
            sprintf('city:%s', $cityName),
            fn() => $this->geocoderInterface->getCoordinatesByCityName($cityName)
        );

        return new CityDto($cityName, $result['lat'], $result['lng']);
    }
}
