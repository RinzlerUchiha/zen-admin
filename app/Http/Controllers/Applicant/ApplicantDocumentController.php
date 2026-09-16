<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantDocument;
use App\Models\Applicant\ApplicantDocumentRequest;
use App\Services\Recruitment\ApplicantDocumentReview;
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
