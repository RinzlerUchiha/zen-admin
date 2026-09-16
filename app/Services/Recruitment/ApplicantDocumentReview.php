<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantDocument;
use App\Models\Applicant\ApplicantDocumentRequest;
use App\Models\Applicant\ApplicantPersonal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HR's completeness check of an applicant's application-stage documents, and
 * the requests HR sends when something is missing or cannot be accepted.
 *
 * The workflow is deliberately small:
 *   applicant uploads → HR accepts, or marks it for replacement (which asks the
 *   applicant for a new file) or requests a missing one → applicant uploads →
 *   HR checks again.
 *
 * Every write runs in a transaction that locks the applicant's row — the same
 * lock the applicant portal takes when uploading — so a decision and a
 * replacement can never interleave.
 */
class ApplicantDocumentReview
{
    public static function types(): array
    {
        return array_keys(config('applicant_documents.types'));
    }

    /** Reasons HR may give for this document type, as [code => config]. */
    public static function reasonsFor(string $type): array
    {
        return collect(config('applicant_documents.review_reasons'))
            ->filter(fn ($reason) => in_array($type, $reason['applies_to'], true))
            ->all();
    }

    /**
     * Where one applicant stands.
     *
     * @return array{slots: Collection, required_total: int, required_accepted: int, complete: bool}
     */
    public static function summary(int $appId): array
    {
        return self::summaries(collect([$appId]))[$appId];
    }

    /**
     * The same summary for many applicants, in two queries regardless of how
     * many there are — used by Applicant Intake.
     *
     * @return array<int, array>
     */
    public static function summaries(Collection $appIds): array
    {
        $appIds = $appIds->filter()->unique()->values();
        $types = self::types();
        $required = config('applicant_documents.required');

        $documents = $appIds->isEmpty() ? collect() : ApplicantDocument::whereIn('app_id', $appIds)
            ->whereIn('doc_type', $types)
            ->orderByDesc('id')
            ->get()
            ->unique(fn ($d) => $d->app_id . '|' . $d->doc_type)
            ->keyBy(fn ($d) => $d->app_id . '|' . $d->doc_type);

        $requests = $appIds->isEmpty() ? collect() : ApplicantDocumentRequest::whereIn('app_id', $appIds)
            ->whereIn('doc_type', $types)
            ->active()
            ->orderByDesc('id')
            ->get()
            ->unique(fn ($r) => $r->app_id . '|' . $r->doc_type)
            ->keyBy(fn ($r) => $r->app_id . '|' . $r->doc_type);

        $out = [];

        foreach ($appIds as $appId) {
            $slots = collect($types)->map(function ($type) use ($appId, $documents, $requests, $required) {
                $document = $documents->get($appId . '|' . $type);

                return [
                    'type' => $type,
                    'label' => config('applicant_documents.types.' . $type),
                    'required' => in_array($type, $required, true),
                    'document' => $document,
                    'request' => $requests->get($appId . '|' . $type),
                    // missing | pending | accepted | rejected
                    'state' => $document ? $document->review_status : 'missing',
                ];
            });

            $requiredSlots = $slots->where('required', true);
            $accepted = $requiredSlots->where('state', 'accepted')->count();

            $out[$appId] = [
                'slots' => $slots,
                'required_total' => $requiredSlots->count(),
                'required_accepted' => $accepted,
                'complete' => $accepted === $requiredSlots->count(),
                'pending' => $slots->where('state', 'pending')->count(),
                'rejected' => $slots->where('state', 'rejected')->count(),
                'open_requests' => $slots->filter(fn ($s) => $s['request'])->count(),
            ];
        }

        return $out;
    }

    public static function accept(ApplicantDocument $document, string $versionToken, string $empno): void
    {
        self::inLock($document->app_id, function () use ($document, $versionToken, $empno) {
            $current = self::currentOrFail($document, $versionToken);

            $current->update([
                'review_status' => 'accepted',
                'review_reason' => null,
                'review_note' => null,
                'reviewed_by' => $empno,
                'reviewed_at' => now(),
            ]);

            // What HR asked for has arrived and is acceptable.
            ApplicantDocumentRequest::where('app_id', $current->app_id)
                ->where('doc_type', $current->doc_type)
                ->active()
                ->update(['status' => 'closed', 'closed_by' => $empno, 'closed_at' => now()]);
        });
    }

    /**
     * The document cannot be accepted. Recording why is also the request for a
     * replacement: the applicant is asked for a new file in the same step.
     */
    public static function reject(
        ApplicantDocument $document,
        string $versionToken,
        string $reason,
        ?string $note,
        ?int $applicationId,
        string $empno
    ): void {
        if (!array_key_exists($reason, self::reasonsFor($document->doc_type))) {
            throw ValidationException::withMessages(['reason' => 'That reason does not apply to this document.']);
        }

        if (!empty(config("applicant_documents.review_reasons.$reason.note_required")) && blank($note)) {
            throw ValidationException::withMessages(['note' => 'Please explain in the note — the applicant will see it.']);
        }

        self::inLock($document->app_id, function () use ($document, $versionToken, $reason, $note, $applicationId, $empno) {
            $current = self::currentOrFail($document, $versionToken);
            self::assertApplicationBelongs($applicationId, $current->app_id);

            $current->update([
                'review_status' => 'rejected',
                'review_reason' => $reason,
                'review_note' => $note,
                'reviewed_by' => $empno,
                'reviewed_at' => now(),
            ]);

            $active = ApplicantDocumentRequest::where('app_id', $current->app_id)
                ->where('doc_type', $current->doc_type)
                ->active()
                ->first();

            if ($active) {
                $active->update([
                    'kind' => 'replacement',
                    'status' => 'open',
                    'application_id' => $applicationId ?? $active->application_id,
                ]);
            } else {
                ApplicantDocumentRequest::create([
                    'app_id' => $current->app_id,
                    'application_id' => $applicationId,
                    'doc_type' => $current->doc_type,
                    'kind' => 'replacement',
                    'status' => 'open',
                    'requested_by' => $empno,
                    'requested_at' => now(),
                ]);
            }
        });
    }

    /** Asks for a document the applicant has not sent at all. */
    public static function requestMissing(int $appId, string $type, ?string $note, ?int $applicationId, string $empno): void
    {
        if (!in_array($type, self::types(), true)) {
            throw ValidationException::withMessages(['doc_type' => 'That document type is not requested at this stage.']);
        }

        self::inLock($appId, function () use ($appId, $type, $note, $applicationId, $empno) {
            self::assertApplicationBelongs($applicationId, $appId);

            $onFile = ApplicantDocument::where('app_id', $appId)->where('doc_type', $type)->exists();

            if ($onFile) {
                throw ValidationException::withMessages([
                    'doc_type' => 'The applicant has already sent this document. Check it, and mark it for replacement if it cannot be accepted.',
                ]);
            }

            $alreadyAsked = ApplicantDocumentRequest::where('app_id', $appId)->where('doc_type', $type)->active()->exists();

            if ($alreadyAsked) {
                throw ValidationException::withMessages(['doc_type' => 'This document has already been requested.']);
            }

            ApplicantDocumentRequest::create([
                'app_id' => $appId,
                'application_id' => $applicationId,
                'doc_type' => $type,
                'kind' => 'missing',
                'note' => $note,
                'status' => 'open',
                'requested_by' => $empno,
                'requested_at' => now(),
            ]);
        });
    }

    /**
     * Withdraws a request for a document that was never sent. A replacement
     * request is not withdrawn this way — it ends when HR accepts a file.
     */
    public static function cancel(ApplicantDocumentRequest $request, string $empno): void
    {
        self::inLock($request->app_id, function () use ($request, $empno) {
            $current = ApplicantDocumentRequest::whereKey($request->id)->first();

            if (!$current || $current->kind !== 'missing' || $current->status !== 'open') {
                throw ValidationException::withMessages(['request' => 'This request can no longer be cancelled.']);
            }

            $current->update(['status' => 'cancelled', 'closed_by' => $empno, 'closed_at' => now()]);
        });
    }

    private static function inLock(int $appId, callable $callback): void
    {
        DB::connection('applicant')->transaction(function () use ($appId, $callback) {
            ApplicantPersonal::whereKey($appId)->lockForUpdate()->first();
            $callback();
        });
    }

    /**
     * Re-reads the document under the lock and refuses the decision if the
     * applicant replaced the file after HR opened the page.
     */
    private static function currentOrFail(ApplicantDocument $document, string $versionToken): ApplicantDocument
    {
        $current = ApplicantDocument::where('app_id', $document->app_id)
            ->where('doc_type', $document->doc_type)
            ->orderByDesc('id')
            ->first();

        if (!$current || $current->id !== $document->id || !hash_equals($current->version_token, $versionToken)) {
            throw ValidationException::withMessages([
                'document' => 'The applicant has replaced this document since you opened the page. Please check the new file.',
            ]);
        }

        return $current;
    }

    private static function assertApplicationBelongs(?int $applicationId, int $appId): void
    {
        if ($applicationId === null) {
            return;
        }

        $belongs = DB::connection('applicant')->table('tblapp_applications')
            ->where('id', $applicationId)
            ->where('app_id', $appId)
            ->exists();

        if (!$belongs) {
            throw ValidationException::withMessages(['application_id' => 'That application does not belong to this applicant.']);
        }
    }
}
