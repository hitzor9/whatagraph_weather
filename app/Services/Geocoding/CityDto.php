<?php

declare(strict_types=1);

namespace App\Services\Geocoding;

class CityDto
{
    public function __construct(
        private string $cityName,
        private float  $lat,
        private float  $lng
    )
    {
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }
}
