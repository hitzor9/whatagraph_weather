<?php

declare(strict_types=1);

namespace App\Services\Weather;

interface WeatherForecastInterface
{
    /**
     * @return array{ dt: int, temp: float, pressure:int }[]
    */
    public function getForecast(float $lat, float $lng): array;
}
