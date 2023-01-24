<?php

declare(strict_types=1);

namespace App\Services\Whatagraph;

use App\Exceptions\PayloadException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class WhatagraphAPI
{
    public const DEFAULT_METRICS = [
        [
            'name' => 'Weather',
            'external_id' => WhatagraphAPI::METRIC_WEATHER_KEY,
            'type' => 'float',
            'accumulator' => 'last',
            'negative_ratio' => true,
        ],
        [
            'name' => 'Pressure',
            'external_id' => WhatagraphAPI::METRIC_PRESSURE_KEY,
            'type' => 'float',
            'accumulator' => 'last',
            'negative_ratio' => false,
        ]
    ];
    public const DEFAULT_DIMENSIONS = [
        [
            'name' => 'city',
            'external_id' => WhatagraphAPI::DIMENSION_CITY_KEY,
            'type' => 'string',
        ]
    ];

    private string $apiKey;

    public const DIMENSION_CITY_KEY = 'city';
    public const DIMENSION_DATE_KEY = 'date';
    public const METRIC_WEATHER_KEY = 'weather';
    public const METRIC_PRESSURE_KEY = 'pressure';

    public function __construct()
    {
        $this->apiKey = config('services.whatagraph.key');
    }

    /**
     * @throws PayloadException
    */
    public function postData(array $dataPoints): array
    {
        if (empty($dataPoints)) {
            throw new InvalidArgumentException('Data points shouldn\'t be empty');
        }

        $response = Http::withToken($this->apiKey)->post('https://api.whatagraph.com/v1/integration-source-data', ['data' => $dataPoints]);

        if (!$response->successful()) {
            throw new PayloadException(
                'Error saving datapoints',
                array_merge(['datapoints' => $dataPoints], ['response' => $response->json()])
            );
        }

        return $response->json();
    }

    /**
     * @throws PayloadException
    */
    public function addDimension(array $dimensionSettings): int
    {
        $response = Http::withToken($this->apiKey)->post('https://api.whatagraph.com/v1/integration-dimensions/', $dimensionSettings);

        if (!$response->successful()) {
            throw new PayloadException(
                'Error creating dimension',
                array_merge(['settings' => $dimensionSettings], ['response' => $response->json()])
            );
        }

        return $response->json('data.id');
    }

    /**
     * @throws PayloadException
    */
    public function addMetric(array $metricSettings): int
    {
        $response = Http::withToken($this->apiKey)->post('https://api.whatagraph.com/v1/integration-metrics/', $metricSettings);

        if (!$response->successful()) {
            throw new PayloadException(
                'Error creating metric',
                array_merge(['settings' => $metricSettings], ['response' => $response->json()])
            );
        }

        return $response->json('data.id');
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
