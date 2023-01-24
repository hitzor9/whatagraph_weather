<?php

declare(strict_types=1);

namespace App\Services\Weather;

interface WeatherForecastInterface
{
    public function getForecast(float $lat, float $lng): array;
}
