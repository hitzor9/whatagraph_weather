<?php

declare(strict_types=1);

namespace App\Services\Whatagraph;

use App\Exceptions\PayloadException;
use App\Services\Weather\DailyForecastMetrics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class WhatagraphService
{
    private const DEFAULT_METRICS = [
        [
            'name'          => 'Weather',
            'external_id'   => WhatagraphAPI::METRIC_WEATHER_KEY,
            'type'          => 'float',
            'accumulator'   => 'last',
            'negative_ratio' => true,
        ],
        [
            'name'          => 'Pressure',
            'external_id'   => WhatagraphAPI::METRIC_PRESSURE_KEY,
            'type'          => 'float',
            'accumulator'   => 'last',
            'negative_ratio' => false,
        ]
    ];

    private const DEFAULT_DIMENSIONS = [
        [
            'name'          => 'city',
            'external_id'   => WhatagraphAPI::DIMENSION_CITY_KEY,
            'type'          => 'string',
        ]
    ];

    public function __construct(
        private WhatagraphAPI $whatagraphAPI
    )
    {
    }

    /**
     * @throws PayloadException
     */
    public function pushDataPoint(Collection $collection): void
    {
        $this->whatagraphAPI->postData(
            $collection->map(
                fn(DailyForecastMetrics $metricsDto) => WeatherDataPointFactory::createFromDailyForecastMetrics($metricsDto)
            )->toArray()
        );
    }

    /**
     * Using cache because we don't want to use database or something
     * Assume that we're going to setup metrics and dimensions only once per account
     * Usually It's not always true that 1 account has only one api key, but mostly it is.
    */
    public function setup(): void
    {
        Cache::get(
            $this->getTokenCacheKey(),
            function () {
                $this->setupDimensions();
                $this->setupMetrics();
                Cache::rememberForever($this->getTokenCacheKey(), fn() => 1);
            }
        );
    }

    /**
     * @throws PayloadException
     */
    private function setupDimensions(): void
    {
        foreach (static::DEFAULT_DIMENSIONS as $dimensionSettings)
        {
            $this->whatagraphAPI->addDimension($dimensionSettings);
        }
    }

    /**
     * @throws PayloadException
     */
    private function setupMetrics(): void
    {
        foreach (static::DEFAULT_METRICS as $dimensionSettings)
        {
            $this->whatagraphAPI->addMetric($dimensionSettings);
        }
    }

    private function getTokenCacheKey(): string
    {
        return sprintf('setup_account:%s', $this->whatagraphAPI->getApiKey());
    }
}
