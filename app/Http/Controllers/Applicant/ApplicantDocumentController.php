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
            'Content-Disposition' => $document->content_disposition,
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
            // An application's document process, or "none". Only asked for when
            // a new request has to be created; see DocumentCompletion::resolveChoice.
            'process_id' => 'nullable|string|max:20',
        ], [
            'reason.required' => 'Please choose why the document cannot be accepted.',
        ]);

        ApplicantDocumentReview::reject(
            $this->document($id, $document),
            $validated['version'],
            $validated['reason'],
            $validated['note'] ?? null,
            isset($validated['application_id']) ? (int) $validated['application_id'] : null,
            $this->empno(),
            $validated['process_id'] ?? null
        );

        return back()->with('success', 'Marked for replacement. The applicant has been asked for a new file.');
    }

    public function storeRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'doc_type' => 'required|string',
            'note' => 'nullable|string|max:1000',
            'application_id' => 'nullable|integer',
            'process_id' => 'nullable|string|max:20',
        ]);

        ApplicantDocumentReview::requestMissing(
            (int) $id,
            $validated['doc_type'],
            $validated['note'] ?? null,
            isset($validated['application_id']) ? (int) $validated['application_id'] : null,
            $this->empno(),
            $validated['process_id'] ?? null
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

    /**
     * Starts the document process for ONE open application of this applicant,
     * with its deadline. The application is required: a process always belongs
     * to exactly one.
     */
    public function startProcess(Request $request, $id)
    {
        $validated = $request->validate([
            'application_id' => 'required|integer',
            'deadline_days' => 'nullable|integer|min:1|max:' . config('applicant_documents.completion.deadline_days_max'),
        ], [
            'application_id.required' => 'Choose which application this document process is for.',
        ]);

        $process = DocumentCompletion::start(
            (int) $id,
            (int) $validated['application_id'],
            isset($validated['deadline_days']) ? (int) $validated['deadline_days'] : null,
            $this->empno()
        );

        return back()->with('success', 'Document process started. The deadline is '
            . $process->deadline_at->format('F j, Y') . '.');
    }

    /** Changes the deadline — a deliberate HR act, counted again from today. */
    public function updateProcess(Request $request, $id, $process)
    {
        $validated = $request->validate([
            'deadline_days' => 'required|integer|min:1|max:' . config('applicant_documents.completion.deadline_days_max'),
        ]);

        DocumentCompletion::changeDeadline($this->process($id, $process), (int) $validated['deadline_days'], $this->empno());

        return back()->with('success', 'Deadline updated.');
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
