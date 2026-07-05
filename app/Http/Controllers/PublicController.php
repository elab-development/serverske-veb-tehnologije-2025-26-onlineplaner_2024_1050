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
}
