<?php

namespace Tests\Unit\Services\Whatagraph;

use App\Exceptions\PayloadException;
use App\Services\Whatagraph\WhatagraphAPI;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatagraphAPITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_post_data_should_success(): void
    {
        $dataPoint1 = [
            'city'      => 'Paris',
            'date'      => '2023-12-02',
            'weather'   => '14',
            'pressure'  => '877'
        ];

        $dataPoint2 = [
            'city'      => 'Paris',
            'date'      => '2023-12-03',
            'weather'   => '15',
            'pressure'  => '900'
        ];

        Http::fake(function (Request $request) use (&$caught, $dataPoint1, $dataPoint2) {
            $this->assertEquals('https://api.whatagraph.com/v1/integration-source-data', $request->url());
            $this->assertEquals('POST', $request->method());
            $this->assertEquals($request->data(), ['data' => [$dataPoint1, $dataPoint2]]);

            $caught = true;

            return Http::response([]);
        });

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->postData([$dataPoint1, $dataPoint2]);

        $this->assertTrue($caught);
    }

    public function test_add_dimension_should_success(): void
    {
        $dimension = [1,2,3];

        Http::fake(function (Request $request) use (&$caught, $dimension) {
            $this->assertEquals('https://api.whatagraph.com/v1/integration-dimensions/', $request->url());
            $this->assertEquals('POST', $request->method());
            $this->assertEquals($request->data(), $dimension);

            $caught = true;

            return Http::response(['data' => ['id' => 1]]);
        });

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->addDimension($dimension);

        $this->assertTrue($caught);
    }

    public function test_add_metric_should_success(): void
    {
        $metric = [1,2,3];

        Http::fake(function (Request $request) use (&$caught, $metric) {
            $this->assertEquals('https://api.whatagraph.com/v1/integration-metrics/', $request->url());
            $this->assertEquals('POST', $request->method());
            $this->assertEquals($request->data(), $metric);

            $caught = true;

            return Http::response(['data' => ['id' => 2]]);
        });

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->addMetric($metric);

        $this->assertTrue($caught);
    }

    public function test_post_data_should_throw_an_exception_on_http_request_error(): void
    {
        Http::fake(fn() => Http::response(status: 500));

        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Error saving datapoints');

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->postData([1,2,3]);
    }

    public function test_post_data_should_throw_an_exception_on_empty_points_error(): void
    {
        Http::fake(fn() => Http::response(status: 500));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data points shouldn\'t be empty');

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->postData([]);
    }

    public function test_add_metric_should_throw_an_exception_on_error(): void
    {
        Http::fake(fn() => Http::response(status: 500));

        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Error creating metric');

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->addMetric([1,2,3]);
    }

    public function test_add_dimension_should_throw_an_exception_on_error(): void
    {
        Http::fake(fn() => Http::response(status: 500));

        $this->expectException(PayloadException::class);
        $this->expectExceptionMessage('Error creating dimension');

        $openWeatherAPI = app(WhatagraphAPI::class);
        $openWeatherAPI->addDimension([1,2,3]);
    }
}
