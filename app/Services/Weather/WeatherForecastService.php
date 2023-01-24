<?php

declare(strict_types=1);

namespace App\Services\Weather;

use App\Services\Geocoding\CityDto;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeatherForecastService
{
    public function __construct(
        private WeatherForecastInterface $weatherForecast
    )
    {
    }

    public function getWeeklyForecast(CityDto $coordinatesDto): Collection
    {
        //If we were loading data from the past, we could cache it.
        //But in our case, the forecast for the future may change

        $dailyForecast = $this->weatherForecast->getForecast($coordinatesDto->getLat(), $coordinatesDto->getLng());

        $metricsCollection = collect();

        foreach($dailyForecast['daily'] ?? [] as $forecastDay) {
            $metricsCollection->push(
                new DailyForecastMetrics(
                    $coordinatesDto->getCityName(),
                    Carbon::createFromTimestamp($forecastDay['dt']),
                    $forecastDay['temp']['day'],
                    $forecastDay['pressure'],
                )
            );
        }

        return $metricsCollection;
    }
}
