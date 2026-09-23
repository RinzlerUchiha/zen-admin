<?php

namespace App\Services\Recruitment;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Photos for a Job Specification, shown beside its job postings on the
 * Applicants careers page.
 *
 * They belong to the Job Specification, not to one posting, so every posting
 * of the same position shows the same photos and a re-post needs no re-upload.
 * HR manages them from a posting's panel in Job Postings.
 *
 * Stored like every other zen-admin image: compressed with
 * reduceImageFileSizeToWebP() onto the same disk FileController reads — the
 * company bucket in production, public storage on a development machine —
 * under jobspec-photos/{jspec_id}/{random}.webp. There is no database table:
 * the folder is the list. zen-applicants reads the same folder (its
 * 'job_photos' disk).
 */
class JobSpecPhotos
{
    /** Two on each side of the posting. */
    public const MAX = 4;

    public const FOLDER = 'jobspec-photos';

    /** What a stored photo's name may look like — anything else is refused. */
    public const NAME_PATTERN = '/^[A-Za-z0-9]{24}\.(webp|jpe?g|png)$/';

    public static function disk(): Filesystem
    {
        return Storage::disk(app()->environment('local') ? 'public' : 's3');
    }

    public static function folder(int $specId): string
    {
        return self::FOLDER . '/' . $specId;
    }

    /** @return string[] file names, oldest first */
    public static function list(int $specId): array
    {
        return collect(self::disk()->files(self::folder($specId)))
            ->map(fn ($path) => basename($path))
            ->filter(fn ($name) => preg_match(self::NAME_PATTERN, $name))
            ->sortBy(fn ($name) => self::disk()->lastModified(self::folder($specId) . '/' . $name))
            ->values()
            ->all();
    }

    /** Store a photo (compressed); returns its name. */
    public static function add(int $specId, UploadedFile $file): string
    {
        // The extension comes from the file's content (validated as JPG/PNG),
        // never from the name it was uploaded with.
        $name = Str::random(24) . '.' . ($file->extension() === 'png' ? 'png' : 'jpg');

        try {
            $path = reduceImageFileSizeToWebP(
                app()->environment('local') ? 'public' : 's3',
                $file->getRealPath(),
                400,
                self::folder($specId) . '/' . $name,
                1600,
                1600
            );

            return basename($path);
        } catch (\Throwable $e) {
            // Compression needs PHP's GD extension. Without it the photo is
            // still worth keeping — store the original, as zen-applicants does
            // for applicant documents.
            report($e);
            self::disk()->putFileAs(self::folder($specId), $file, $name);

            return $name;
        }
    }

    public static function delete(int $specId, string $name): bool
    {
        return preg_match(self::NAME_PATTERN, $name) === 1
            && self::disk()->delete(self::folder($specId) . '/' . $name);
    }

    public static function path(int $specId, string $name): ?string
    {
        return preg_match(self::NAME_PATTERN, $name) === 1 ? self::folder($specId) . '/' . $name : null;
    }
}
