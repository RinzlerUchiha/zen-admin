<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\HireflowManpowerPosition;
use App\Models\Recruitment\JobPosting;
use App\Services\Recruitment\JobSpecPhotos;
use Illuminate\Http\Request;

/**
 * The photos shown beside a job posting on the careers page. They are the
 * posting's Job Specification's photos (App\Services\Recruitment\JobSpecPhotos),
 * managed here from the posting HR has open.
 *
 * TWO WAYS IN, ONE SET OF PHOTOS
 *
 * From a posting HR already has open, and from the Create Job Posting form,
 * where the posting does not exist yet. That is not a special case: a photo
 * belongs to the Job Specification, never to a posting, so the position HR is
 * creating the posting FOR already identifies the right folder. Pictures can
 * therefore be chosen while writing the ad instead of after saving it, which
 * is when HR has them to hand.
 *
 * Both doors reach the same folder and the same four-photo limit. A photo
 * added from the create form and then abandoned without saving stays on the
 * Job Specification -- which is correct, and the same thing that happens when
 * HR adds one from a posting and later closes it.
 */
class JobPostingPhotoController extends Controller
{
    public function index(JobPosting $jobPosting)
    {
        return response()->json($this->payload($jobPosting));
    }

    public function store(Request $request, JobPosting $jobPosting)
    {
        $refused = $this->accept($request, $this->specId($jobPosting));

        return $refused ?? response()->json($this->payload($jobPosting));
    }

    /* ------------------------------------------------------------------
     | The same four, addressed by position, for the Create Posting form.
     | No posting exists yet; the Job Specification the photos belong to
     | does, and the position names it.
     ------------------------------------------------------------------ */

    public function draftIndex(HireflowManpowerPosition $position)
    {
        return response()->json($this->draftPayload($position));
    }

    public function draftStore(Request $request, HireflowManpowerPosition $position)
    {
        $refused = $this->accept($request, $this->positionSpecId($position));

        return $refused ?? response()->json($this->draftPayload($position));
    }

    public function draftDestroy(HireflowManpowerPosition $position, string $name)
    {
        abort_unless(JobSpecPhotos::delete($this->positionSpecId($position), $name), 404);

        return response()->json($this->draftPayload($position));
    }

    public function draftShow(HireflowManpowerPosition $position, string $name)
    {
        return $this->stream($this->positionSpecId($position), $name);
    }

    /**
     * Validate and store one photo against a Job Specification.
     *
     * Returns null when it was stored, or the response explaining why it was
     * not — so both doors refuse a 6 MB bitmap, a PDF renamed .jpg, and a
     * fifth photo in exactly the same words.
     */
    private function accept(Request $request, int $specId)
    {
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

        return null;
    }

    public function destroy(JobPosting $jobPosting, string $name)
    {
        abort_unless(JobSpecPhotos::delete($this->specId($jobPosting), $name), 404);

        return response()->json($this->payload($jobPosting));
    }

    /** Streams one photo for HR's preview. */
    public function show(JobPosting $jobPosting, string $name)
    {
        return $this->stream($this->specId($jobPosting), $name);
    }

    private function stream(int $specId, string $name)
    {
        $path = JobSpecPhotos::path($specId, $name);
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

    /** The same question asked of a position, for the create form. */
    private function positionSpecId(HireflowManpowerPosition $position): int
    {
        abort_unless($position->jobspec_id, 422, 'This position is not linked to a Job Specification.');

        return (int) $position->jobspec_id;
    }

    private function payload(JobPosting $jobPosting): array
    {
        return $this->listing(
            $this->specId($jobPosting),
            fn ($name) => route('recruitment.job-postings.photos.show', [$jobPosting, $name])
        );
    }

    private function draftPayload(HireflowManpowerPosition $position): array
    {
        return $this->listing(
            $this->positionSpecId($position),
            fn ($name) => route('recruitment.job-postings.draft.photos.show', [$position, $name])
        );
    }

    private function listing(int $specId, callable $url): array
    {
        return [
            'max' => JobSpecPhotos::MAX,
            'photos' => collect(JobSpecPhotos::list($specId))->map(fn ($name) => [
                'name' => $name,
                'url' => $url($name),
            ])->all(),
        ];
    }
}
