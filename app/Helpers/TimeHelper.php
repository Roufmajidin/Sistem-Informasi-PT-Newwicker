<?php
if (! function_exists('formatMenit')) {
    function formatMenit($menit) {
        if ($menit >= 60) {
            $jam  = floor($menit / 60);
            $sisa = $menit % 60;
            return $jam . ' jam ' . ($sisa > 0 ? $sisa . ' menit' : '');
        }
        return $menit . ' menit';
    }
}
