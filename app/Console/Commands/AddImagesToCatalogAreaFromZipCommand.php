<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\CatalogArea;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AddImagesToCatalogAreaFromZipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco:add_images_to_catalog_areas {path : The path to the zip file on the server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add images to CatalogArea models from a zip file based on the position of the images';


    /**
     * Execute the console command.
     */



    public function handle()
    {
        $path = $this->argument('path');

        // Extract the zip file
        $zip = new \ZipArchive();
        $zip->open($path);
        $extractPath = storage_path('app/extracted_images');
        $zip->extractTo($extractPath);
        $zip->close();

        // Get all extracted image files
        $imageFiles = Storage::allFiles('extracted_images');

        // Process each image file
        foreach ($imageFiles as $imageFile) {
            // Get GPS coordinates from image file
            $imageFile = storage_path('app/'.$imageFile);
            $gps = $this->getGPSFromImage($imageFile);

            if (!$gps) {
                $this->info('No GPS coordinates found for image: '.$imageFile);
                continue;
            }
            // Find CatalogArea model based on GPS coordinates
            $catalogArea = DB::select("SELECT * FROM catalog_areas WHERE ST_Contains(geometry(geometry), ST_SetSRID(ST_MakePoint(".$gps['longitude'].", ".$gps['latitude']."), 4326)::geometry)");

            if ($catalogArea && count($catalogArea) > 0) {
                // Associate the image with the CatalogArea
                $catalogArea = CatalogArea::find($catalogArea[0]->id);

                $fileName = pathinfo($imageFile, PATHINFO_BASENAME);
                $existingMedia = $catalogArea->getMedia('gallery')->where('file_name', $fileName)->first();
                if (!$existingMedia) {
                    $catalogArea->addMedia($imageFile)->toMediaCollection('gallery');
                }
            }
        }

        $this->info('Images added successfully.');
    }

    private function getGPSFromImage($imageFile)
    {
        $allowedExtensions = ['png', 'jpeg', 'jpg', 'webp'];
        $fileExtension = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            return null;
        }
        // Get exif data from the image
        $exif = exif_read_data($imageFile);

        // Check if exif data contains GPS information
        if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLongitude'])) {
            $latitude = $this->getGPSCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
            $longitude = $this->getGPSCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);

            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        return null;
    }

    private function getGPSCoordinate($exifCoord, $hemi)
    {
        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    private function gps2Num($coordPart)
    {

        $parts = explode('/', $coordPart);

        if (count($parts) <= 0) {
            return 0;
        }

        if (count($parts) == 1) {
            return $parts[0];
        }

        return floatval($parts[0]) / floatval($parts[1]);
    }
}
