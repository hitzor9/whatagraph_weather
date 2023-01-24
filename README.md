## Installation

Clone repository 

```
git clone git@github.com:hitzor9/whatagraph_weather.git .
```

Install dependencies
```
composer install
```

Copy .env.example file and insert your openweather api key and whatagraph api key in .env
```
cp .env.example .env && php artisan key:generate
```

Run docker containers with sail

```
./vendor/bin/sail up
```

Launch import as simple artisan cli command. You can specify any number of cities in command arguments

```
./vendor/bin/sail artisan weather:sync-forecast Chicago
./vendor/bin/sail artisan weather:sync-forecast Chicago Barcelona "New York" Berlin
```

Launch autotests

```
./vendor/bin/sail artisan test 
```
