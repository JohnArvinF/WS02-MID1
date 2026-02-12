<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WindyController extends Controller
{
    public function forecast(Request $request)
    {
        $response = Http::post(
            'https://api.windy.com/api/point-forecast/v2',
            [
                'lat' => $request->lat,
                'lon' => $request->lon,
                'model' => 'gfs',
                'parameters' => [
                    'temp',
                    'wind',
                    'precip',
                    'rh'
                ],
                'levels' => ['surface'],
                'key' => config('services.windy.key'),
            ]
        );

        return response()->json($response->json());
    }
}
