<?php

declare(strict_types=1);

namespace App\Services\Weather;

use App\Exceptions\PayloadException;
use App\Services\Geocoding\GeocoderInterface;
use Illuminate\Support\Facades\Http;

class OpenWeatherAPI implements GeocoderInterface, WeatherForecastInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key');
    }

    /**
     * @throws PayloadException
     * @return array{ lat: float, lng: float }
    */
    public function getCoordinatesByCityName(string $cityName): array
    {
        $response = Http::get('https://api.openweathermap.org/geo/1.0/direct', [
            'q'     => $cityName,
            'APPID' => $this->apiKey,
            'limit' => 1
        ]);

        if (!$response->successful()) {
            throw new PayloadException(
                'Error getting coordinates by city name',
                array_merge(['city' => $cityName], ['error' => $response->json()])
            );
        }

        if (empty($response->json())) {
            throw new PayloadException(
                'Coordinates for city not found',
                array_merge(['city' => $cityName], ['error' => $response->json()])
            );
        }

        return ['lat' => $response->json('0.lat'), 'lng' => $response->json('0.lon')];
    }

    /**
     * I don't want to operate with Dto object here to avoid high coupling between API and business logic layers
     *
     * @throws PayloadException
    */
    public function getForecast(float $lat, float $lng): array
    {
        $response = Http::get('https://api.openweathermap.org/data/3.0/onecall', [
            'lat'       => $lat,
            'lon'       => $lng,
            'appid'     => $this->apiKey,
            'exclude'   => 'minutely,hourly',
        ]);

        if (!$response->successful()) {
            throw new PayloadException(
                'Error getting forecast by coordinates',
                array_merge(['lat' => $lat, 'lng' => $lng], ['error' => $response->json()])
            );
        }

        return array_map(fn($item) => [
            'dt'        => $item['dt'],
            'temp'      => $item['temp']['day'],
            'pressure'  => $item['pressure']
        ], $response->json('daily'));
    }
}
