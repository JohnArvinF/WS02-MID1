<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function forecast(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $lat = $request->lat;
        $lon = $request->lon;

        // Cache to protect free quota
        return Cache::remember(
            "weatherapi_{$lat}_{$lon}",
            now()->addMinutes(30),
            function () use ($lat, $lon) {

                $response = Http::get(
                    'https://api.weatherapi.com/v1/forecast.json',
                    [
                        'key' => config('services.weatherapi.key'),
                        'q' => "{$lat},{$lon}",
                        'days' => 7,
                        'aqi' => 'no',
                        'alerts' => 'no',
                    ]
                );

                return $response->json();
            }
        );
    }
}
