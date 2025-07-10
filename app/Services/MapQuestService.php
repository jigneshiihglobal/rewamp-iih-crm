<?php

namespace App\Services;

use App\Contracts\GeocodingAPI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapQuestService implements GeocodingAPI
{
    private $key;

    public function __construct()
    {
        $this->key = config('services.mapquest.key');
    }

    public function reverseGeocode(float $lat, float $long): string
    {

        try {

            $response = Http::post("https://www.mapquestapi.com/geocoding/v1/reverse?key=" . $this->key, [
                "location" => [
                    "latLng" => [
                        "lat" => $lat,
                        "lng" => $long
                    ]
                ],
            ]);

            if (!$response->successful()) {
                return "";
            }

            $response_json = $response->json();
            $street = $response_json['results'][0]['locations'][0]['street'] ?? '';
            $adminArea6 = $response_json['results'][0]['locations'][0]['adminArea6'] ?? '';
            $adminArea5 = $response_json['results'][0]['locations'][0]['adminArea5'] ?? '';
            $adminArea4 = $response_json['results'][0]['locations'][0]['adminArea4'] ?? '';
            $adminArea3 = $response_json['results'][0]['locations'][0]['adminArea3'] ?? '';
            $adminArea1 = $response_json['results'][0]['locations'][0]['adminArea1'] ?? '';
            $postalCode = $response_json['results'][0]['locations'][0]['postalCode'] ?? '';

            return implode(", ", [$street, $adminArea6, $adminArea5, $adminArea4, $adminArea3, $adminArea1, $postalCode]);
        } catch (\Throwable $th) {
            return "";
        }
    }
}
