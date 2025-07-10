<?php

namespace App\Contracts;

interface GeocodingAPI {
    public function reverseGeocode(float $lat, float $long) : string;
}
