<?php

namespace App\Console\Commands;

use App\Services\Geocoding\GeocoderService;
use App\Services\Weather\WeatherForecastService;
use App\Services\Whatagraph\WhatagraphService;
use Illuminate\Console\Command;

class ExportWeatherDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:sync-forecast {cities*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download 7-days weather forecast for chosen cities and upload data to whatagraph';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (empty($this->argument('cities'))) {
            $this->error('Cities not set');
        }

        $geocoderService = app(GeocoderService::class);
        $forecastService = app(WeatherForecastService::class);

        /**
         * @var GeocoderService $geocoderService
         * @var WeatherForecastService $forecastService
         * @var WhatagraphService $whatagraphService
         */

        $metricsCollection = collect();
        foreach ($this->argument('cities') as $cityName) {
            $this->getOutput()->writeln(sprintf('Getting forecast for %s', $cityName));
            $coordinates = $geocoderService->getCityCoordinates($cityName);
            $metricsCollection = $metricsCollection->merge($forecastService->getWeeklyForecast($coordinates));
        }

        if (isset($metricsCollection) && $metricsCollection->isNotEmpty()) {
            $this->getOutput()->writeln(sprintf('%d datapoints to push', $metricsCollection->count()));

            try {
                //In case we need push a big amount of data points It might be better to push data with batches of 1000 events
                //In case amount of data going to be extremely huge, but strict size per city, we can push it by city-based batches.
                //For example for weekly forecast for 1000 cities we can accumulate data points for 100 cities and push it separately to avoid memory overflow

                $whatagraphService = app(WhatagraphService::class);
                $whatagraphService->setup();
                $whatagraphService->pushDataPoint($metricsCollection);
            } catch (\Throwable $e) {
                $this->getOutput()->error($e->getMessage());
                report($e);

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
