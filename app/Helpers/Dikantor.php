<?php

if (!function_exists('diKantor')) {
    /**
     * Cek apakah koordinat berada di dalam radius kantor
     *
     * @param float|string $latUser
     * @param float|string $lonUser
     * @param float $tolerance Toleransi tambahan meter (default 50 m)
     * @return bool
     */
    function diKantor($latUser, $lonUser, $tolerance = 50): bool
    {
        $officeLat = config('office.lat');
        $officeLon = config('office.lon');
        $officeRadius = config('office.radius', 100); // meter

        if (empty($latUser) || empty($lonUser)) {
            return false;
        }

        // Hitung jarak user ke kantor
        $jarak = hitungJarak($latUser, $lonUser, $officeLat, $officeLon);

        // Cek apakah jarak <= radius + toleransi
        return $jarak <= ($officeRadius + $tolerance);
    }
}
