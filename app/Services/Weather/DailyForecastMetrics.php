<?php

declare(strict_types=1);

namespace App\Services\Weather;

use Carbon\Carbon;

class DailyForecastMetrics
{
    public function __construct(
        private string $city,
        private Carbon $datetime,
        private float $temp,
        private int $pressure,
    )
    {
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getDatetime(): Carbon
    {
        return $this->datetime;
    }

    public function getTemp(): float
    {
        return $this->temp;
    }

    public function getPressure(): int
    {
        return $this->pressure;
    }
}
