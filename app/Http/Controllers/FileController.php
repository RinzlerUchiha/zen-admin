<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function showProfileImg($filename)
    {
        // return $this->serveFile('emp-img', $filename);
        $path = '';
        foreach (['jpg', 'JPG', 'png', 'PNG', 'webp'] as $ext) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/zen/assets/image/img/' . $filename . '.' . $ext;
            if (file_exists($path)) {
                break;
            }
        }

        if (!file_exists($path)) {
            // abort(404);
            $path = public_path('no-file.png');
        }

        $mime = mime_content_type($path);
        $content = file_get_contents($path);

        return response($content, 200)->header('Content-Type', $mime);
    }

    public function serveFile($src, $filename)
    {
        // Check if the user is authorized to access the file
        if (!Auth::check()) {
            // If not authorized or the file doesn't exist, return a 404 response
            abort(404, 'File not found or unauthorized');
        }

        $filename = basename($filename);

        $source = [
            'emp-img' => $_SERVER['DOCUMENT_ROOT'].'/zen/assets/image/img',
            'license' => $_SERVER['DOCUMENT_ROOT'].'/zen/assets/license',
            'certificate' => $_SERVER['DOCUMENT_ROOT'].'/zen/assets/certificate',
            'contract' => $_SERVER['DOCUMENT_ROOT'].'/zen/assets/contract',
            'clearance' => $_SERVER['DOCUMENT_ROOT'].'/upload_files_here'
        ];

        $path = ($source[$src] ?? '') . '/' . $filename;

        if(!file_exists($path)){
            foreach (['jpg', 'JPG', 'png', 'PNG', 'webp', 'pdf', 'xls', 'xlsx'] as $ext) {
                $path = ($source[$src] ?? '') . '/' . $filename . '.' . $ext;
                if (file_exists($path)) {
                    break;
                }
            }
        }

        if (!file_exists($path)) {
            // abort(404);
            $path = public_path('no-file.png');
        }

        $mime = mime_content_type($path);
        $content = file_get_contents($path);

        return response($content, 200)->header('Content-Type', $mime);
    }

    public static function serveFileFromS3($src, $filename)
    {        
        $source = [
            'announcement' => 'announcements',
            'event' => 'events',
            'memo' => 'memo',
            'emp-img' => 'images/employees',
            'license' => 'licenses',
            'certificate' => 'certificates',
            'contract' => 'contracts',
            'clearance' => 'clearance',
            'ir' => 'ir'
        ];

        // Path inside storage
        $path = ($source[$src] ?? '') . '/' . $filename;

        // Choose your disk (local, public, or s3)
        $disk = Storage::disk(app()->environment('local') ? 'public' : 's3');

        // $path2 = '';
        // $f_path = dirname($path);
        // $f_base = pathinfo($path, PATHINFO_FILENAME);

        // $exists = collect($disk->files($f_path))
        //     ->filter(function ($file) use ($f_base) {
        //         return strcasecmp(
        //             pathinfo($file, PATHINFO_FILENAME),
        //             $f_base
        //         ) === 0;
        //     });

        // if($exists->first()){
        //     $path2 = $exists->first();
        // }

        // Check if the exact key exists (cache the boolean)
        $exists = Cache::remember("s3_exists:$path", now()->addMinutes(10), fn () => $disk->exists($path));
        
        // Check if file exists
        if (! $exists) {
            // abort(404, 'File not found.');

            $allFiles = $disk->files($source[$src] ?? ''); // or specific folder
            $path = collect($allFiles)->first(function ($file) use ($filename) {
                return pathinfo($file, PATHINFO_FILENAME) === $filename;
            });

            if (!$path) {
                // fallback default image
                return response()->file(public_path('no-file.png'));
            }
        }

        // Get file contents
        // $content = $disk->get($path);

        // // Detect mime type
        // $mime = $disk->mimeType($path);
        // // Fallback if mimeType() not supported (older Laravel/S3)
        // if (!$mime) {
        //     $tmpFile = tempnam(sys_get_temp_dir(), 'laravel-file');
        //     file_put_contents($tmpFile, $content);
        //     $mime = mime_content_type($tmpFile);
        //     unlink($tmpFile);
        // }

        // // Return response
        // return response($content, 200)
        //     ->header('Content-Type', $mime);


        // Stream from S3 — hides bucket; sets browser cache
        $stream = $disk->readStream($path);
        if (! $stream) return response()->file(public_path('no-file.png'));

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->stream(function() use ($stream) {
            fpassthru($stream);
            is_resource($stream) && fclose($stream);
        }, 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400', // cache in browser for a day
            'ETag'          => md5($path),               // cheap validator; or store real ETag in cache
        ]);
    }

    public static function serveFileFromS3ForApplicant($src, $filename)
    {
        $source = [
            'app-img' => 'images',
            'license' => 'licenses',
            'certificate' => 'certificates',
            'contract' => 'contracts',
            'basic-abstract-reasoning' => 'basic-abstract-reasoning',
            'maya-test' => 'maya-test',
            'disc-asset' => 'disc-asset',
        ];

        // Path inside storage
        $path = 'applicant/' . ($source[$src] ?? '') . '/' . $filename;

        // Choose your disk (local, public, or s3)
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(app()->environment('local') ? 'public' : 's3');

        $path2 = '';
        $f_path = dirname($path);
        $f_base = pathinfo($path, PATHINFO_FILENAME);

        $exists = collect($disk->files($f_path))
            ->filter(function ($file) use ($f_base) {
                return strcasecmp(
                    pathinfo($file, PATHINFO_FILENAME),
                    $f_base
                ) === 0;
            });

        if($exists->first()){
            $path2 = $exists->first();
        }

        // Check if the exact key exists (cache the boolean)
        $exists = Cache::remember("s3_exists:$path", now()->addMinutes(1), fn () => $disk->exists($path2 ?: $path));
        if($path2){
            $path = $path2;
        }
        
        // Check if file exists
        if (! $exists) {
            // abort(404, 'File not found.');

            $allFiles = $disk->files($source[$src] ?? ''); // or specific folder
            $path = collect($allFiles)->first(function ($file) use ($filename) {
                return pathinfo($file, PATHINFO_FILENAME) === $filename;
            });

            if (!$path) {
                // fallback default image
                return response()->file(public_path('no-file.png'));
            }
        }

        $stream = $disk->readStream($path);
        if (! $stream) return response()->file(public_path('no-file.png'));

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->stream(function() use ($stream) {
            fpassthru($stream);
            is_resource($stream) && fclose($stream);
        }, 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400', // cache in browser for a day
            'ETag'          => md5($path),               // cheap validator; or store real ETag in cache
        ]);
    }

    function fileExistsOnS3($path) {
        return Cache::remember("s3_exists_{$path}", now()->addWeek(), function () use ($path) {
            return Storage::disk('s3')->exists($path);
        });
    }


    public function fileTemporaryUrl($src, $filename)
    {
        $source = [
            'announcement' => 'announcements',
            'event' => 'events',
            'memo' => 'memo',
            'emp-img' => 'images/employees',
            'license' => 'licenses',
            'certificate' => 'certificates',
            'contract' => 'contracts',
            'clearance' => 'clearance',
            'ir' => 'ir'
        ];

        // Path inside storage
        $path = ($source[$src] ?? '') . '/' . $filename;

        // Optional: check user permissions
        // if (!auth()->user()->canAccessFile($file)) {
        //     abort(403, 'Unauthorized access.');
        // }

        // Choose your disk (local, public, or s3)
        $disk = Storage::disk('s3');

        // Check if the exact key exists (cache the boolean)
        $exists = Cache::remember("s3_exists:$path", now()->addMinutes(10), fn () => $disk->exists($path));

        // Check if file exists
        if (! $exists) {
            abort(404, 'File not found.');
        }
        
        // Generate a temporary (signed) S3 URL
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $url = $disk->temporaryUrl(
            $path, // S3 key
            now()->addMinutes(5)
        );

        // Option 1: Redirect to the signed S3 URL (recommended for large files)
        return redirect($url);

        // Option 2: Stream through Laravel (adds load to your server, but gives full control)
        // return Response::stream(function () use ($file) {
        //     echo Storage::disk('s3')->get($file->path);
        // }, 200, [
        //     'Content-Type' => $file->mime_type,
        //     'Content-Disposition' => 'attachment; filename="' . $file->name . '"',
        // ]);
    }
}
