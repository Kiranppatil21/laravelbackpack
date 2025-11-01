<?php

namespace App\Services;

class GeoValidator
{
    protected $allowedMeters;

    public function __construct(int $allowedMeters = 100)
    {
        $this->allowedMeters = $allowedMeters;
    }

    /**
     * Haversine distance check between two lat/lon pairs (meters) and compare to allowed radius.
     */
    public function isWithinRadius(float $lat1, float $lon1, float $lat2, float $lon2): bool
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance <= $this->allowedMeters;
    }
}
