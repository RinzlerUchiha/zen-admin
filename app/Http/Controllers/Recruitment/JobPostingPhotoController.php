<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\JobPosting;
use App\Services\Recruitment\JobSpecPhotos;
use Illuminate\Http\Request;

/**
 * The photos shown beside a job posting on the careers page. They are the
 * posting's Job Specification's photos (App\Services\Recruitment\JobSpecPhotos),
 * managed here from the posting HR has open.
 */
class JobPostingPhotoController extends Controller
{
    public function index(JobPosting $jobPosting)
    {
        return response()->json($this->payload($jobPosting));
    }

    public function store(Request $request, JobPosting $jobPosting)
    {
        $specId = $this->specId($jobPosting);

        $request->validate(
            ['photo' => 'required|file|mimes:jpg,jpeg,png|max:5120'],
            [
                'photo.required' => 'Choose a photo to upload.',
                'photo.mimes' => 'Use a JPG or PNG photo.',
                'photo.max' => 'That photo is too large. The limit is 5 MB.',
            ]
        );

        if (count(JobSpecPhotos::list($specId)) >= JobSpecPhotos::MAX) {
            return response()->json(['message' => 'This job already has ' . JobSpecPhotos::MAX . ' photos. Remove one first.'], 422);
        }

        try {
            JobSpecPhotos::add($specId, $request->file('photo'));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'That photo could not be saved. Please try another file.'], 500);
        }

        return response()->json($this->payload($jobPosting));
    }

    public function destroy(JobPosting $jobPosting, string $name)
    {
        abort_unless(JobSpecPhotos::delete($this->specId($jobPosting), $name), 404);

        return response()->json($this->payload($jobPosting));
    }

    /** Streams one photo for HR's preview. */
    public function show(JobPosting $jobPosting, string $name)
    {
        $path = JobSpecPhotos::path($this->specId($jobPosting), $name);
        $disk = JobSpecPhotos::disk();
        abort_unless($path && $disk->exists($path), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function specId(JobPosting $jobPosting): int
    {
        abort_unless($jobPosting->jobspec_id, 422, 'This posting is not linked to a Job Specification.');

        return (int) $jobPosting->jobspec_id;
    }

    private function payload(JobPosting $jobPosting): array
    {
        $specId = $this->specId($jobPosting);

        return [
            'max' => JobSpecPhotos::MAX,
            'photos' => collect(JobSpecPhotos::list($specId))->map(fn ($name) => [
                'name' => $name,
                'url' => route('recruitment.job-postings.photos.show', [$jobPosting, $name]),
            ])->all(),
        ];
    }
}
