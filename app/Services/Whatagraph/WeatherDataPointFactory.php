<?php

declare(strict_types=1);

namespace App\Services\Whatagraph;

use App\Services\Weather\DailyForecastMetrics;

class WeatherDataPointFactory
{
    public static function createFromDailyForecastMetrics(DailyForecastMetrics $dailyForecastMetrics): WeatherDataPoint
    {
        return new WeatherDataPoint(
            $dailyForecastMetrics->getCity(),
            $dailyForecastMetrics->getDatetime(),
            $dailyForecastMetrics->getTemp(),
            $dailyForecastMetrics->getPressure()
        );
    }
}
