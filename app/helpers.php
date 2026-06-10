<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

if (! function_exists('getNameInitials')) {
    function getNameInitials($string) {
        // Split the string by spaces to get all words
        $words = explode(' ', $string);
        
        // Initialize an empty string to store the initials
        $initials = '';

        // Loop through each word and get the first letter
        foreach ($words as $word) {
            // Only consider non-empty words
            if (!empty($word)) {
                $initials .= strtoupper($word[0]).'.'; // Append the uppercase first letter of each word
            }
        }

        return $initials; // Return the initials
    }
}

if (!function_exists('get_root_url')) {
    function get_root_url($url)
    {
        $parsedUrl = parse_url($url);print_r($parsedUrl);
        $path = $parsedUrl['path'] ?? ''; // "/v1/users/12345"
        
        // Extract the version from the first segment
        $segments = explode('/', trim($path, '/'));
        return !empty($segments[0]) ? '/'.$segments[0] : null; // returns "v1"
    }
}

if (!function_exists('check_assign')) {
    function check_assign($empno, $for, $dept = false)
    {
        $data = DB::connection('hrd2')->select("SELECT ".($dept == false ? "auth_assignation" : "auth_dept")." AS col1 FROM tbl_dept_authority WHERE auth_emp = ? AND auth_for = ?", [$empno, $for]);
        $result = $data[0] ?? null;
        return $result?->col1 ? str_replace("|", ",", $result?->col1) : '';
    }
}

if (!function_exists('safeDate')) {
    function safeDate($date) {
        return empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00' ? '' : $date;
    }
}

if (!function_exists('reduceImageFileSizeToWebP')) {
    function reduceImageFileSizeToWebP($disk, $imagePath, $targetSizeKB = 100, $outputPath = null, $maxWidth = null, $maxHeight = null)
    {
        $isConvertible = in_array(mime_content_type($imagePath), [
            'image/jpeg', 
            'image/png'
        ]);

        $encodedImage = null; // Variable to hold the encoded image

        // Create an instance of ImageManager (this replaces Image::make() in v3)
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($imagePath); // Load the image

        if(!$isConvertible){
            $encodedImage = $image->encode();
            if ($outputPath) {
                Storage::disk($disk)->put($outputPath, $encodedImage);
                return $outputPath;
            }
            return $encodedImage;
        }

        // Check if WebP is supported by the current driver
        $webpSupported = $manager->driver()->supports('webp');
        // $webpSupported = false;
        if($webpSupported){
            $outputPath = pathinfo($outputPath, PATHINFO_DIRNAME).'/'.pathinfo($outputPath, PATHINFO_FILENAME).'.webp';
        }
        
        // if (!$webpSupported) {
        //     throw new \Exception('WebP format is not supported by your image driver.');
        // }

        // Resize the image if it exceeds the max dimensions
        if (($maxWidth && $image->width() > $maxWidth) || ($maxHeight && $image->height() > $maxHeight)) {
            $image->scale($maxWidth, $maxHeight);
        }

        // Get the current image size in kilobytes
        $currentSizeKB = filesize($imagePath) / 1024;

        // If the image is already smaller than the target size, no need to compress
        if ($currentSizeKB <= $targetSizeKB) {
            // If an output path is provided, save the WebP directly
            if ($outputPath) {
                if ($webpSupported) {
                    $encodedImage = $image->encode(new WebpEncoder());
                    Storage::disk($disk)->put($outputPath, $encodedImage); // Save as WebP with 90% quality
                } else {
                    $encodedImage = $image->encode();
                    Storage::disk($disk)->put($outputPath, $encodedImage); // Save as original format (e.g., JPEG/PNG)
                }
                return $outputPath;
            }
            return $encodedImage;
        }

        // Set initial quality for the WebP image (you can adjust this to balance quality vs. size)
        $quality = 90;

        // If WebP is supported, try to reduce the size to target by converting to WebP
        if ($webpSupported) {
            while ($currentSizeKB > $targetSizeKB && $quality > 20) {
                // Encode the image in WebP format with the current quality
                $encodedImage = $image->encode(new WebpEncoder(quality: $quality));

                // Check the size of the encoded image in memory
                $currentSizeKB = strlen($encodedImage) / 1024; // in KB

                // Reduce the quality further
                $quality -= 5;
            }

            // If an output path is provided, save the final WebP encoded image to that location
            if ($outputPath) {
                Storage::disk($disk)->put($outputPath, $encodedImage);
                return $outputPath;
            }
        } else {
            // If WebP is not supported, just save it in the original format and reduce size if necessary
            while ($currentSizeKB > $targetSizeKB && $quality > 20) {
                // Encode the image in its original format (e.g., JPEG or PNG) with the current quality
                $encodedImage = $image->encode(new AutoEncoder(quality: $quality));

                // Check the size of the encoded image in memory
                $currentSizeKB = strlen($encodedImage) / 1024; // in KB

                // Reduce the quality further
                $quality -= 10;
            }

            // If an output path is provided, save the final encoded image to that location
            if ($outputPath) {
                Storage::disk($disk)->put($outputPath, $encodedImage);
                return $outputPath;
            }
        }

        return $encodedImage;
    }
}

// create app/helper.php
// add this in composer.json
// "autoload": {
//     "files": [
//         "app/helpers.php"
//     ]
// }
// Run composer dump-autoload