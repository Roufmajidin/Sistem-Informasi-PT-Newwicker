<?php

if (!function_exists('hitungJarak')) {
    /**
     * Hitung jarak antara dua koordinat (lat, lon) dalam meter
     *
     * @param float|string $lat1
     * @param float|string $lon1
     * @param float|string $lat2
     * @param float|string $lon2
     * @return float
     */
    function hitungJarak($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meter

        $lat1 = deg2rad((float)$lat1);
        $lon1 = deg2rad((float)$lon1);
        $lat2 = deg2rad((float)$lat2);
        $lon2 = deg2rad((float)$lon2);

        $latDiff = $lat2 - $lat1;
        $lonDiff = $lon2 - $lon1;

        $a = pow(sin($latDiff / 2), 2) +
             cos($lat1) * cos($lat2) * pow(sin($lonDiff / 2), 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

if (!function_exists('formatJarak')) {
    /**
     * Format jarak dalam meter atau kilometer
     *
     * @param float $meter
     * @return string
     */
    function formatJarak(float $meter): string
    {
        if ($meter >= 1000) {
            return number_format($meter / 1000, 2) . ' km';
        }
        return number_format($meter, 1) . ' m';
    }
}
