<?php

declare(strict_types=1);

namespace App\Services\Weather;

interface WeatherForecastInterface
{
    /**
     * I don't want to operate with Dto object here to avoid high coupling between API and business logic layers
     */
    public function getForecast(float $lat, float $lng): array;
}
