<?php

declare(strict_types=1);

namespace App\Services\Whatagraph;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class WeatherDataPoint implements Arrayable
{
    public function __construct(
        private string $city,
        private Carbon $datetime,
        private float $temperature,
        private float $pressure,
    )
    {
    }

    public function toArray(): array
    {
        return [
            WhatagraphAPI::DIMENSION_CITY_KEY  => $this->city,
            WhatagraphAPI::DIMENSION_DATE_KEY  => $this->datetime->format('Y-m-d'),
            WhatagraphAPI::METRIC_WEATHER_KEY  => $this->temperature,
            WhatagraphAPI::METRIC_PRESSURE_KEY => $this->pressure
        ];
    }
}
