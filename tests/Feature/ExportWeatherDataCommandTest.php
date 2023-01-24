<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExportWeatherDataCommandTest extends TestCase
{
    public function test_command_should_success(): void
    {
        Http::preventStrayRequests();

        $requestedCities = [];

        $data = [
            'Istanbul' =>  ['lat' => 222.0, 'lon' => 333.0, 'forecast' => ['daily' => [['dt' => 30000, 'temp' => ['day' => 30], 'pressure' => 825]]]],
            'Berlin'    =>  ['lat' => 123.0, 'lon' => 345.0, 'forecast' => ['daily' => [['dt' => 100000, 'temp' => ['day' => 25], 'pressure' => 800]]]],
            'Barcelona' =>  ['lat' => 321.0, 'lon' => 543.0, 'forecast' => ['daily' => [['dt' => 2000000, 'temp' => ['day' => 35], 'pressure' => 850]]]]
        ];

        $expectedDataPoints = [
            ['city' => 'Istanbul',  'date' => '1970-01-01', 'weather' => 30.0, 'pressure' => 825.0],
            ['city' => 'Berlin',    'date' => '1970-01-02', 'weather' => 25.0, 'pressure' => 800.0],
            ['city' => 'Barcelona', 'date' => '1970-01-24', 'weather' => 35.0, 'pressure' => 850.0]
        ];

        Http::fake([
            'https://api.openweathermap.org/geo/1.0/direct*' => function(Request $request) use (&$requestedCities, $data) {
                $city = $request->data()['q'];
                $requestedCities[] = $city;

                return Http::response([['lat' => $data[$city]['lat'], 'lon' => $data[$city]['lon']]]);
            },
            'api.openweathermap.org/data/3.0/onecall*' => function(Request $request) use (&$data) {

                $cityData = array_shift($data);

                $this->assertEquals($cityData['lat'], $request->data()['lat']);
                $this->assertEquals($cityData['lon'], $request->data()['lon']);

                return $cityData['forecast'];
            },
            'api.whatagraph.com/v1/integration-dimensions/' => Http::response(['data' => ['id' => 1]]),
            'api.whatagraph.com/v1/integration-metrics/' => Http::response(['data' => ['id' => 1]]),
            'api.whatagraph.com/v1/integration-source-data' => function(Request $request) use ($expectedDataPoints) {
                $this->assertEquals(['data' => $expectedDataPoints], $request->data());

                return Http::response([]);
            },
        ]);

        $this->artisan('weather:sync-forecast Istanbul Berlin Barcelona')->assertExitCode(0);

        $this->assertEquals(['Istanbul', 'Berlin', 'Barcelona'], $requestedCities);
        $this->assertEmpty($data);
    }
}
