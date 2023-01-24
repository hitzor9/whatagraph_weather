<?php

namespace Tests\Unit\Services\Weather;

use App\Services\Geocoding\CityDto;
use App\Services\Weather\DailyForecastMetrics;
use App\Services\Weather\WeatherForecastInterface;
use App\Services\Weather\WeatherForecastService;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

class WeatherForecastServiceTest extends TestCase
{
    public function test_weather_forecast_should_success(): void
    {
        $this->mock(WeatherForecastInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getForecast')
                ->with(10.0, 20.0)
                ->times(1)
                ->andReturn([
                    'daily' => [
                        [
                            'dt' => 10000,
                            'temp' => ['day' => 20],
                            'pressure' => 888
                        ],
                        [
                            'dt' => 20000,
                            'temp' => ['day' => 25],
                            'pressure' => 988
                        ]
                    ]
                ]);
        });

        $result = app(WeatherForecastService::class)->getWeeklyForecast(new CityDto('Berlin', 10, 20));
        /**@var Collection $result*/

        $this->assertCount(2, $result);
        $this->assertInstanceOf(DailyForecastMetrics::class, $result[0]);
        $this->assertInstanceOf(DailyForecastMetrics::class, $result[1]);

        $this->assertEquals('Berlin', $result[0]->getCity());
        $this->assertEquals(10000, $result[0]->getDateTime()->getTimestamp());
        $this->assertEquals(20, $result[0]->getTemp());
        $this->assertEquals(888, $result[0]->getPressure());

        $this->assertEquals('Berlin', $result[1]->getCity());
        $this->assertEquals(20000, $result[1]->getDateTime()->getTimestamp());
        $this->assertEquals(25, $result[1]->getTemp());
        $this->assertEquals(988, $result[1]->getPressure());
    }

    public function test_weather_forecast_should_return_empty_collection_when_no_results(): void
    {
        $this->mock(WeatherForecastInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getForecast')
                ->with(10.0, 20.0)
                ->times(1)
                ->andReturn([]);
        });

        $result = app(WeatherForecastService::class)->getWeeklyForecast(new CityDto('Berlin', 10, 20));
        /**@var Collection $result*/

        $this->assertEmpty($result);
    }
}
