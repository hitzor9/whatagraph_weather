<?php

namespace Tests\Unit\Services\Whatagraph;

use App\Services\Weather\DailyForecastMetrics;
use App\Services\Whatagraph\WhatagraphAPI;
use App\Services\Whatagraph\WhatagraphService;
use Carbon\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class WhatagraphServiceTest extends TestCase
{
    public function test_setup_calls_api_only_once(): void
    {
        $this->mock(WhatagraphAPI::class, function (MockInterface $mock) {
            $mock->shouldReceive('getApiKey')->andReturn('123');
            $mock->shouldReceive('addDimension')->once();
            $mock->shouldReceive('addMetric')->twice();
        });

        $whatagraphService = app(WhatagraphService::class);

        $whatagraphService->setup();
        $whatagraphService->setup();
        $whatagraphService->setup();
    }

    public function test_push_data_points_should_success(): void
    {
        $this->mock(WhatagraphAPI::class, function (MockInterface $mock) {
            $mock->shouldReceive('getApiKey')->andReturn('123');
            $mock->shouldReceive('postData')->once()
                ->with([
                    [
                        "city"      => "Berlin",
                        "date"      => "1970-01-01",
                        "weather"   => 20.0,
                        "pressure"  => 900.0,
                    ],
                    [
                        'city'      => 'Berlin',
                        "date"      => "1970-01-03",
                        'weather'   => 25.0,
                        'pressure'  => 850.0
                    ]
                ]);
        });

        app(WhatagraphService::class)->pushDataPoint(collect([
            new DailyForecastMetrics('Berlin', Carbon::createFromTimestamp(10000), 20, 900),
            new DailyForecastMetrics('Berlin', Carbon::createFromTimestamp(200000), 25, 850)
        ]));
    }
}
