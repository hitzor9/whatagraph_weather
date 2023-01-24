<?php

namespace Tests\Unit\Services\Geocoding;

use App\Services\Geocoding\CityDto;
use App\Services\Geocoding\GeocoderInterface;
use App\Services\Geocoding\GeocoderService;
use Mockery\MockInterface;
use Tests\TestCase;

class GeocodingServiceTest extends TestCase
{
    public function test_geocoding_service_should_success()
    {
        $this->mock(GeocoderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCoordinatesByCityName')
                ->with('Yerevan')
                ->once()
                ->andReturn(['lat' => 12, 'lng' => 13]);
        });

        $result = app(GeocoderService::class)->getCityCoordinates('Yerevan');
        /**@var CityDto $result*/

        $this->assertEquals('Yerevan', $result->getCityName());
        $this->assertEquals(12.0, $result->getLat());
        $this->assertEquals(13.0, $result->getLng());
    }

    public function test_geocoding_service_should_call_geocoder_api_once_and_cache_results()
    {
        $this->mock(GeocoderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCoordinatesByCityName')
                ->with('Yerevan')
                ->once()
                ->andReturn(['lat' => 12, 'lng' => 13]);
        });

        app(GeocoderService::class)->getCityCoordinates('Yerevan');
        app(GeocoderService::class)->getCityCoordinates('Yerevan');
    }
}
