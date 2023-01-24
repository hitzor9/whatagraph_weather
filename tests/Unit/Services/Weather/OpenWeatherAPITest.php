<?php

namespace Tests\Unit\Services\Weather;

use App\Exceptions\PayloadException;
use App\Services\Weather\OpenWeatherAPI;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWeatherAPITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_get_coordinates_by_city_name_should_success(): void
    {
        Http::fake(fn() => Http::response([123]));

        $openWeatherAPI = app(OpenWeatherAPI::class);
        $openWeatherAPI->getCoordinatesByCityName('Barcelona');

        Http::assertSent(function (Request $request) {
            return $request['q'] === 'Barcelona' && $request['limit'] === 1;
        });
    }

    public function test_get_forecast_should_success(): void
    {
        $caught = false;

        Http::fake(function (Request $request) use (&$caught) {
            $this->assertEquals(10.0, $request->data()['lat'] ?? null);
            $this->assertEquals(11.0, $request->data()['lon'] ?? null);
            $this->assertEquals( "minutely,hourly", $request->data()['exclude'] ?? null);

            $caught = true;

            return Http::response(['daily' => []]);
        });

        $openWeatherAPI = app(OpenWeatherAPI::class);
        $openWeatherAPI->getForecast(10.0, 11.0);

        $this->assertTrue($caught);
    }

    public function test_get_coordinates_by_city_name_should_throw_an_exception_on_error(): void
    {
        Http::fake(fn() => Http::response(status: 500));

        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Error getting coordinates by city name');

        $openWeatherAPI = app(OpenWeatherAPI::class);
        $openWeatherAPI->getCoordinatesByCityName('Barcelona');
    }

    public function test_get_forecast_should_throw_an_exception_on_error(): void
    {
        Http::fake(fn() => Http::response(status: 500));

        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Error getting forecast by coordinates');

        $openWeatherAPI = app(OpenWeatherAPI::class);
        $openWeatherAPI->getForecast(10.0, 11.0);
    }

    public function test_get_coordinates_by_city_name_should_throw_an_error_for_wrong_city(): void
    {
        Http::fake(fn() => Http::response([]));

        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Coordinates for city not found');

        $openWeatherAPI = app(OpenWeatherAPI::class);
        $openWeatherAPI->getCoordinatesByCityName('zvge12sd');
    }
}
