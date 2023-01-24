<?php

namespace App\Providers;

use App\Services\Geocoding\GeocoderInterface;
use App\Services\Weather\OpenWeatherAPI;
use App\Services\Weather\WeatherForecastInterface;
use App\Services\Whatagraph\WhatagraphAPI;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        ServerProvider::class => DigitalOceanServerProvider::class,
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OpenWeatherAPI::class, fn() => new OpenWeatherAPI());
        $this->app->singleton(WhatagraphAPI::class, fn() => new WhatagraphAPI());

        //Perhaps in the future we will want to change the implementation of the geocoder and the forecast services, so they are separated into interfaces
        //All components are stateless, so can use them as singletons
        $this->app->singleton(GeocoderInterface::class, fn($app) => $app->make(OpenWeatherAPI::class));
        $this->app->singleton(WeatherForecastInterface::class, fn($app) => $app->make(OpenWeatherAPI::class));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
