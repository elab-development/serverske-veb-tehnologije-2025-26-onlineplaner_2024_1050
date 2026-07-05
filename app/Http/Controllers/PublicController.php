<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PublicController extends Controller
{
    public function holidays(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['sometimes', 'integer', 'min:1970', 'max:2100'],
            'country' => ['sometimes', 'string', 'size:2'],
        ]);

        $year = $validated['year'] ?? now()->year;
        $country = strtoupper($validated['country'] ?? 'RS');

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get("https://date.nager.at/api/v3/publicholidays/{$year}/{$country}");
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Holiday service is currently unavailable.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Holiday service returned an error.',
                'status' => $response->status(),
            ], 502);
        }

        return response()->json([
            'source' => 'Nager.Date',
            'year' => $year,
            'country' => $country,
            'holidays' => $response->json(),
        ]);
    }

    public function weather(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'forecast_days' => ['sometimes', 'integer', 'min:1', 'max:16'],
            'timezone' => ['sometimes', 'string', 'max:64'],
        ]);

        $latitude = $validated['latitude'] ?? 44.8125;
        $longitude = $validated['longitude'] ?? 20.4612;
        $forecastDays = $validated['forecast_days'] ?? 7;
        $timezone = $validated['timezone'] ?? 'Europe/Belgrade';

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'forecast_days' => $forecastDays,
                    'timezone' => $timezone,
                    'current' => 'temperature_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,weather_code',
                ]);
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Weather service is currently unavailable.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Weather service returned an error.',
                'status' => $response->status(),
            ], 502);
        }

        return response()->json([
            'source' => 'Open-Meteo',
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
            'forecast_days' => (int) $forecastDays,
            'timezone' => $timezone,
            'weather' => $response->json(),
        ]);
    }
}
