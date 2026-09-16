<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantDocument;
use App\Models\Applicant\ApplicantDocumentProcess;
use App\Models\Applicant\ApplicantDocumentRequest;
use App\Services\Recruitment\ApplicantDocumentReview;
use App\Services\Recruitment\DocumentCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * HR's actions on an applicant's application-stage documents. The page itself
 * is the "documents" tab of ApplicantProfileController::show.
 *
 * Authorization is on the routes (can:applicant-documents.*). Every lookup is
 * also scoped to the applicant in the URL, so a document or request id that
 * belongs to someone else is simply not found.
 */
class ApplicantDocumentController extends Controller
{
    public function file($id, $document)
    {
        $document = $this->document($id, $document);
        $disk = Storage::disk(config('applicant_documents.disk'));

        if (!$disk->exists($document->storage_path)) {
            abort(404);
        }

        $stream = $disk->readStream($document->storage_path);

        if (!$stream) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            try {
                if (is_resource($stream)) {
                    fpassthru($stream);
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            // From the upload allow-list (PDF, JPEG, PNG, WebP) — never taken
            // from the file or its name.
            'Content-Type' => $document->doc_mime,
            'Content-Disposition' => 'inline; filename="' . str_replace(['"', "\r", "\n"], '', $document->doc_original_name) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store',
        ]);
    }

    public function accept(Request $request, $id, $document)
    {
        $validated = $request->validate(['version' => 'required|string']);

        ApplicantDocumentReview::accept($this->document($id, $document), $validated['version'], $this->empno());

        return back()->with('success', 'Document accepted.');
    }

    public function reject(Request $request, $id, $document)
    {
        $validated = $request->validate([
            'version' => 'required|string',
            'reason' => 'required|string',
            'note' => 'nullable|string|max:1000',
            'application_id' => 'nullable|integer',
        ], [
            'reason.required' => 'Please choose why the document cannot be accepted.',
        ]);

        ApplicantDocumentReview::reject(
            $this->document($id, $document),
            $validated['version'],
            $validated['reason'],
            $validated['note'] ?? null,
            isset($validated['application_id']) ? (int) $validated['application_id'] : null,
            $this->empno()
        );

        return back()->with('success', 'Marked for replacement. The applicant has been asked for a new file.');
    }

    public function storeRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'doc_type' => 'required|string',
            'note' => 'nullable|string|max:1000',
            'application_id' => 'nullable|integer',
        ]);

        ApplicantDocumentReview::requestMissing(
            (int) $id,
            $validated['doc_type'],
            $validated['note'] ?? null,
            isset($validated['application_id']) ? (int) $validated['application_id'] : null,
            $this->empno()
        );

        return back()->with('success', 'Document requested from the applicant.');
    }

    public function cancelRequest($id, $request)
    {
        $documentRequest = ApplicantDocumentRequest::where('id', $request)
            ->where('app_id', $id)
            ->firstOrFail();

        ApplicantDocumentReview::cancel($documentRequest, $this->empno());

        return back()->with('success', 'Request cancelled.');
    }

    /* =====================================================================
     * Document completion process (HireFlow 2.5 · M3)
     * ===================================================================== */

    /** Starts the run: one deadline and one attempt allowance for the set. */
    public function startProcess(Request $request, $id)
    {
        $validated = $request->validate([
            'deadline_days' => 'nullable|integer|min:1|max:' . config('applicant_documents.completion.deadline_days_max'),
            'max_attempts' => 'nullable|integer|min:1|max:' . config('applicant_documents.completion.max_attempts_max'),
            'application_id' => 'nullable|integer',
        ]);

        $process = DocumentCompletion::start(
            (int) $id,
            isset($validated['application_id']) ? (int) $validated['application_id'] : null,
            isset($validated['deadline_days']) ? (int) $validated['deadline_days'] : null,
            isset($validated['max_attempts']) ? (int) $validated['max_attempts'] : null,
            $this->empno()
        );

        return back()->with('success', 'Document completion started. The applicant has until '
            . $process->deadline_at->format('F j, Y') . '.');
    }

    /** Changes the deadline. Deliberate, never a side effect of anything else. */
    public function updateProcess(Request $request, $id, $process)
    {
        $validated = $request->validate([
            'deadline_days' => 'nullable|integer|min:1|max:' . config('applicant_documents.completion.deadline_days_max'),
            'max_attempts' => 'nullable|integer|min:1|max:' . config('applicant_documents.completion.max_attempts_max'),
        ]);

        $model = $this->process($id, $process);

        if (!empty($validated['deadline_days'])) {
            DocumentCompletion::changeDeadline($model, (int) $validated['deadline_days'], $this->empno());
        }

        if (!empty($validated['max_attempts'])) {
            DocumentCompletion::changeMaxAttempts($model, (int) $validated['max_attempts'], $this->empno());
        }

        return back()->with('success', 'Document completion updated.');
    }

    private function process($appId, $processId): ApplicantDocumentProcess
    {
        return ApplicantDocumentProcess::where('id', $processId)
            ->where('app_id', $appId)
            ->firstOrFail();
    }

    /** An application-stage document belonging to the applicant in the URL. */
    private function document($appId, $documentId): ApplicantDocument
    {
        return ApplicantDocument::where('id', $documentId)
            ->where('app_id', $appId)
            ->whereIn('doc_type', ApplicantDocumentReview::types())
            ->firstOrFail();
    }

    private function empno(): string
    {
        return (string) auth()->user()->Emp_No;
    }
}
