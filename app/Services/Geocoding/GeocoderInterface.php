<?php

declare(strict_types=1);

namespace App\Services\Geocoding;

interface GeocoderInterface
{
    /**
     * @return array{ lat: float, lng: float }
     */
    public function getCoordinatesByCityName(string $cityName): array;
}
