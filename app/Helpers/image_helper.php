<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    public static function generateThumb(
        string $sourcePath,
        string $destPath,
        int $width,
        int $height
    ): void {
        $manager = new ImageManager(new Driver());

        $image = $manager
            ->read($sourcePath)
            ->cover($width, $height)
            ->toWebp(80);

        Storage::put($destPath, (string) $image);
    }
}
