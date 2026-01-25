<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageController extends Controller
{
   public function generateAllThumbs()
{
    $disk = Storage::disk('public');

    $sourceDir = 'products';
    $thumbDir  = 'products/thumbs';

    if (!$disk->exists($sourceDir)) {
        return response()->json([
            'message' => 'Source folder not found',
            'path' => $sourceDir
        ]);
    }

    if (!$disk->exists($thumbDir)) {
        $disk->makeDirectory($thumbDir);
    }

    $files = $disk->files($sourceDir);

    if (empty($files)) {
        return response()->json([
            'message' => 'No images found',
            'path' => $sourceDir
        ]);
    }

    $manager = new \Intervention\Image\ImageManager(
        new \Intervention\Image\Drivers\Gd\Driver()
    );

    $generated = 0;
    $skipped = 0;

    foreach ($files as $file) {
        $filename = basename($file);
        $thumbPath = $thumbDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp';

        if ($disk->exists($thumbPath)) {
            $skipped++;
            continue;
        }

        try {
            $image = $manager
                ->read($disk->get($file))
                ->cover(400, 400)
                ->toWebp(75);

            $disk->put($thumbPath, (string) $image);
            $generated++;
        } catch (\Throwable $e) {
            logger()->error($e->getMessage());
        }
    }

    return response()->json([
        'message' => 'Done',
        'generated' => $generated,
        'skipped' => $skipped,
        'total' => count($files),
    ]);
}

}
