<?php

if (!function_exists('hitungJarak')) {
    function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $lat1 = deg2rad((float)$lat1);
        $lon1 = deg2rad((float)$lon1);
        $lat2 = deg2rad((float)$lat2);
        $lon2 = deg2rad((float)$lon2);

        $latDiff = $lat2 - $lat1;
        $lonDiff = $lon2 - $lon1;

        $a = sin($latDiff / 2) ** 2 +
             cos($lat1) * cos($lat2) * sin($lonDiff / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // meter
    }
}

if (!function_exists('formatJarak')) {
    function formatJarak($meter)
    {
        if ($meter >= 1000) {
            return number_format($meter / 1000, 2) . ' km';
        }
        return number_format($meter, 1) . ' m';
    }
}
