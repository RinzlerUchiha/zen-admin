@extends('pages.applicant.profile')

@push('styles')
<style>
    #applicant-documents { font-size: 13px; }

    .adoc-summary {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        border: 1px solid var(--zn-line); border-radius: var(--zn-radius-lg); padding: 12px 16px; margin-bottom: 14px; background: var(--zn-surface);
    }
    .adoc-summary b { font-size: 14px; }

    .adoc-badge {
        display: inline-block; padding: 2px 10px; border-radius: 999px;
        font-size: 11.5px; font-weight: 600; white-space: nowrap;
    }
    .adoc-missing  { background: var(--zn-surface-2); color: var(--zn-ink-2); }
    .adoc-pending  { background: var(--zn-accent-soft); color: var(--zn-accent-dark); }
    .adoc-accepted { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .adoc-rejected { background: var(--zn-warn-soft); color: var(--zn-warn); }
    .adoc-complete { background: var(--zn-ok-soft); color: var(--zn-ok); }
    .adoc-incomplete { background: var(--zn-caution-soft); color: var(--zn-caution); }
    /* Document deadlines: one per application, shown apart from the
       per-document table because the documents are shared across the
       applicant's applications and the deadlines are not. */
    .adoc-process {
        border: 1px solid var(--zn-line); border-left: 3px solid var(--zn-accent-dark); border-radius: var(--zn-radius-lg);
        padding: 12px 16px; margin-bottom: 14px; background: var(--zn-surface);
    }
    .adoc-process-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .adoc-process-row {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        border-top: 1px solid var(--zn-surface-2); padding-top: 10px; margin-top: 10px;
    }
    .adoc-process-facts { display: flex; gap: 22px; flex-wrap: wrap; }
    .adoc-process-facts div { font-size: 12px; color: var(--zn-ink-3); }
    .adoc-process-facts b { display: block; font-size: 13.5px; color: var(--zn-ink); }
    .adoc-overdue { color: var(--zn-warn) !important; }

    #applicant-documents-table thead th {
        font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .05em;
        color: var(--zn-ink-3); border-bottom: 1px solid var(--zn-line); white-space: nowrap;
    }
    #applicant-documents-table td { vertical-align: top; padding-top: 12px; padding-bottom: 12px; }
    .adoc-sub { color: var(--zn-ink-3); font-size: 12px; }
    .adoc-note { font-size: 12px; margin-top: 4px; color: var(--zn-accent-dark); }
    .adoc-preview { width: 100%; height: 70vh; border: 0; }
    .adoc-preview-img { max-width: 100%; max-height: 70vh; display: block; margin: 0 auto; }
</style>
@endpush

@section('profile_content')
@php
    $summary = $documentSummary;
    $canReview = Gate::allows('applicant-documents.review');
    $stateLabel = [
        'missing' => 'Not sent',
        'pending' => 'Waiting for check',
        'accepted' => 'Accepted',
        'rejected' => 'Needs replacement',
    ];
@endphp

<div id="applicant-documents">

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    {{-- Say why there are no actions, rather than leaving a reviewer to guess
         whether the page is broken. --}}
    @unless ($canReview)
        <div class="alert alert-secondary py-2 mb-3" style="font-size:12.5px">
            You can view these documents. Accepting a document, asking for a replacement or requesting a
            missing document needs the HRIS <b>Employee Application Profile</b> permission with
            <b>Direct Edit</b> or <b>Hire</b>.
        </div>
    @endunless

    {{-- Completeness at a glance: the question HR opens this tab to answer. --}}
    <div class="adoc-summary">
        <div>
            <b>Application documents</b>
            <div class="adoc-sub">
                {{ $summary['required_accepted'] }} of {{ $summary['required_total'] }} required documents accepted
                @if ($summary['pending']) · {{ $summary['pending'] }} waiting for check @endif
                @if ($summary['open_requests']) · {{ $summary['open_requests'] }} requested from the applicant @endif
            </div>
        </div>
        <span class="adoc-badge {{ $summary['complete'] ? 'adoc-complete' : 'adoc-incomplete' }}">
            {{ $summary['complete'] ? 'Complete' : 'Incomplete' }}
        </span>
    </div>

    {{-- Document deadlines (HireFlow 2.5 · M3). A document process belongs to ONE
         application; each open application can have its own. Adding a request
         never moves a deadline — only "Change deadline" does. --}}
    @php
        $processes = $summary['processes'];
        $runningFor = $processes->pluck('application_id')->filter()->all();
        $startable = $applicantApplications->filter(
            fn ($application) => !$application->is_closed && !in_array($application->id, $runningFor)
        );
        $titleOf = fn ($applicationId) => $documentApplications[$applicationId] ?? ('Application #' . $applicationId);
    @endphp

    <div class="adoc-process">
        <div class="adoc-process-head">
            <div>
                <b>Document deadlines</b>
                <div class="adoc-sub">
                    One per application. The documents below are the applicant's own and count for every application.
                </div>
            </div>
            @if ($canReview && $startable->isNotEmpty())
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-process-start">
                    Start document process
                </button>
            @endif
        </div>

        @forelse ($processes as $process)
            @php
                $overdue = $process->deadline_at->isPast();
                // Calendar days between the dates, counted exactly as the applicant
                // portal counts them, so HR and the applicant see the same number.
                $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($process->deadline_at->copy()->startOfDay(), false));
            @endphp
            <div class="adoc-process-row" data-process="{{ $process->id }}">
                <div class="adoc-process-facts">
                    <div>
                        <b>{{ $titleOf($process->application_id) }}</b>
                        Started {{ $process->started_at->format('M j, Y') }} by {{ $process->started_by }}
                    </div>
                    <div>
                        <b class="{{ $overdue ? 'adoc-overdue' : '' }}">{{ $process->deadline_at->format('M j, Y') }}</b>
                        {{ $process->deadline_days }} days, holidays excluded
                    </div>
                    <div>
                        <b class="{{ $overdue ? 'adoc-overdue' : '' }}">
                            {{ $overdue ? 'Overdue' : ($daysLeft === 0 ? 'Due today' : $daysLeft . ' ' . Str::plural('day', $daysLeft) . ' left') }}
                        </b>
                        {{ $overdue ? 'Closes as Non-Responsive on the next nightly run if requests are still open' : 'Time remaining' }}
                    </div>
                </div>
                @if ($canReview)
                    <button type="button" class="btn btn-sm btn-outline-secondary js-change-deadline"
                            data-bs-toggle="modal" data-bs-target="#modal-process-deadline"
                            data-action="{{ route('applicant.documents.completion.update', ['id' => $applicant->app_id, 'process' => $process->id]) }}"
                            data-title="{{ $titleOf($process->application_id) }}"
                            data-days="{{ $process->deadline_days }}">Change deadline</button>
                @endif
            </div>
        @empty
            <div class="adoc-sub mt-2">
                No document deadline is running.
                @if ($canReview && $startable->isEmpty())
                    {{ $applicantApplications->isEmpty()
                        ? 'The applicant has not applied to a position yet.'
                        : 'None of the applicant\'s applications is open.' }}
                @endif
            </div>
        @endforelse
    </div>

    <div class="table-responsive">
        <table class="table table-sm" id="applicant-documents-table">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>File</th>
                    <th>Status</th>
                    @if ($canReview)<th class="text-end">Actions</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($summary['slots'] as $slot)
                    @php
                        $document = $slot['document'];
                        $request = $slot['request'];
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $slot['label'] }}</div>
                            <div class="adoc-sub">{{ $slot['required'] ? 'Required' : 'Optional' }}</div>
                        </td>

                        <td>
                            @if ($document)
                                <a href="#" class="js-preview" data-bs-toggle="modal" data-bs-target="#docPreviewModal"
                                   data-src="{{ route('applicant.documents.file', ['id' => $document->app_id, 'document' => $document->id]) }}"
                                   data-pdf="{{ $document->is_pdf ? 1 : 0 }}"
                                   data-title="{{ $slot['label'] }}">{{ $document->doc_original_name }}</a>
                                <div class="adoc-sub">
                                    {{ $document->size_for_humans }} · sent {{ $document->uploaded_at?->format('M j, Y g:i A') }}
                                </div>
                            @else
                                <span class="adoc-sub">—</span>
                            @endif
                        </td>

                        <td>
                            <span class="adoc-badge adoc-{{ $slot['state'] }}">{{ $stateLabel[$slot['state']] }}</span>

                            @if ($slot['state'] === 'rejected')
                                <div class="adoc-note"><b>{{ $document->review_reason_label }}</b>@if ($document->review_note) — {{ $document->review_note }}@endif</div>
                            @endif

                            @if ($document?->reviewed_at)
                                <div class="adoc-sub">Checked {{ $document->reviewed_at->format('M j, Y') }} by {{ $document->reviewed_by }}</div>
                            @endif

                            @if ($request)
                                <div class="adoc-sub">
                                    @if ($request->status === 'submitted')
                                        Applicant responded {{ $request->submitted_at?->format('M j, Y') }}
                                    @else
                                        Requested {{ $request->requested_at?->format('M j, Y') }} — waiting for applicant
                                    @endif
                                    @if ($request->application_id && isset($documentApplications[$request->application_id]))
                                        · {{ $documentApplications[$request->application_id] }}
                                    @endif
                                </div>
                                @if ($request->kind === 'missing' && $request->note)
                                    <div class="adoc-note">“{{ $request->note }}”</div>
                                @endif
                            @endif
                        </td>

                        @if ($canReview)
                            <td class="text-end text-nowrap">
                                @if ($document)
                                    @if ($document->review_status !== 'accepted')
                                        <form method="POST" class="d-inline"
                                              action="{{ route('applicant.documents.accept', ['id' => $document->app_id, 'document' => $document->id]) }}">
                                            @csrf
                                            <input type="hidden" name="version" value="{{ $document->version_token }}">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Accept</button>
                                        </form>
                                    @endif
                                    @if ($document->review_status !== 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reject"
                                                data-bs-toggle="modal" data-bs-target="#docRejectModal"
                                                data-action="{{ route('applicant.documents.reject', ['id' => $document->app_id, 'document' => $document->id]) }}"
                                                data-version="{{ $document->version_token }}"
                                                data-type="{{ $slot['type'] }}"
                                                data-has-request="{{ $request ? 1 : 0 }}"
                                                data-title="{{ $slot['label'] }}">Needs replacement</button>
                                    @endif
                                @elseif ($request)
                                    @if ($request->kind === 'missing' && $request->status === 'open')
                                        <form method="POST" class="d-inline"
                                              action="{{ route('applicant.documents.request.cancel', ['id' => $applicant->app_id, 'request' => $request->id]) }}"
                                              onsubmit="return confirm('Cancel this request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Cancel request</button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary js-request"
                                            data-bs-toggle="modal" data-bs-target="#docRequestModal"
                                            data-type="{{ $slot['type'] }}"
                                            data-title="{{ $slot['label'] }}">Request</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="adoc-sub mb-0">
        When the applicant replaces a document it comes back as “Waiting for check” — an earlier decision never
        carries over to a new file.
    </p>
</div>

{{-- Preview. The file is streamed through zen-admin after the permission check;
     it is never a public link. --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-labelledby="docPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h1 class="modal-title fs-6" id="docPreviewTitle">Document</h1>
                <a href="#" class="btn btn-sm btn-outline-secondary ms-auto me-2" id="docPreviewOpen" target="_blank" rel="noopener">Open in new tab</a>
                <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="docPreviewBody"></div>
        </div>
    </div>
</div>

@if ($canReview)
    @php
        $reasonsByType = collect($summary['slots'])->mapWithKeys(fn ($slot) => [
            $slot['type'] => collect(\App\Services\Recruitment\ApplicantDocumentReview::reasonsFor($slot['type']))
                ->map(fn ($r, $code) => ['code' => $code, 'label' => $r['label'], 'hint' => $r['hint'], 'note_required' => !empty($r['note_required'])])
                ->values(),
        ]);
    @endphp

    {{-- Starting a document process: always for one chosen, open application. --}}
    @if ($startable->isNotEmpty())
        <div class="modal fade" id="modal-process-start" tabindex="-1" aria-labelledby="modalProcessStartTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" class="modal-content"
                      action="{{ route('applicant.documents.completion.start', $applicant->app_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-6" id="modalProcessStartTitle">Start document process</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="processApplication">Application</label>
                            <select class="form-select form-select-sm" name="application_id" id="processApplication" required>
                                @if ($startable->count() > 1)
                                    <option value="" selected disabled>Choose an application…</option>
                                @endif
                                @foreach ($startable as $application)
                                    <option value="{{ $application->id }}">{{ $application->posting_title }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">The deadline and its outcome belong to this application only.</div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label" for="processDeadlineDays">Days to complete</label>
                            <input type="number" class="form-control form-control-sm" name="deadline_days"
                                   id="processDeadlineDays" min="1" required
                                   max="{{ config('applicant_documents.completion.deadline_days_max') }}"
                                   value="{{ config('applicant_documents.completion.deadline_days') }}">
                            <div class="form-text">
                                Calendar days from today. Weekends count; Philippine public holidays do not.
                                Adding requests later does not move the deadline.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Start</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Changing a running process's deadline: deliberate, counted from today. --}}
    @if ($processes->isNotEmpty())
        <div class="modal fade" id="modal-process-deadline" tabindex="-1" aria-labelledby="modalProcessDeadlineTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" class="modal-content" id="processDeadlineForm">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-6" id="modalProcessDeadlineTitle">Change deadline</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="processDeadlineChange">Days to complete, from today</label>
                        <input type="number" class="form-control form-control-sm" name="deadline_days"
                               id="processDeadlineChange" min="1" required
                               max="{{ config('applicant_documents.completion.deadline_days_max') }}">
                        <div class="form-text">Weekends count; Philippine public holidays do not.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="modal fade" id="docRejectModal" tabindex="-1" aria-labelledby="docRejectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content" id="docRejectForm">
                @csrf
                <input type="hidden" name="version" id="docRejectVersion">
                <div class="modal-header">
                    <h1 class="modal-title fs-6" id="docRejectTitle">Needs replacement</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="adoc-sub">The applicant is asked for a new file and sees the reason and your note.</p>
                    <div class="mb-3">
                        <label class="form-label" for="docRejectReason">Reason</label>
                        <select class="form-select form-select-sm" name="reason" id="docRejectReason" required></select>
                        <div class="form-text" id="docRejectHint"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="docRejectNote">Note to the applicant <span class="adoc-sub" id="docRejectNoteOpt">(optional)</span></label>
                        <textarea class="form-control form-control-sm" name="note" id="docRejectNote" rows="3" maxlength="1000"></textarea>
                    </div>
                    @if ($processes->isNotEmpty())
                        {{-- Which application's document process a NEW request counts towards.
                             HR chooses; with several running there is no default. --}}
                        <div class="mb-3 js-process-choice">
                            <label class="form-label" for="docRejectProcess">Document deadline</label>
                            <select class="form-select form-select-sm js-process-select" name="process_id" id="docRejectProcess" @if ($processes->count() > 1) required @endif>
                                @if ($processes->count() > 1)
                                    <option value="" selected disabled>Choose an application…</option>
                                @endif
                                @foreach ($processes as $process)
                                    <option value="{{ $process->id }}">{{ $titleOf($process->application_id) }} — due {{ $process->deadline_at->format('M j, Y') }}</option>
                                @endforeach
                                <option value="none">No document deadline</option>
                            </select>
                        </div>
                    @endif
                    @if ($documentApplications->isNotEmpty())
                        <div class="js-application-context">
                            <label class="form-label" for="docRejectApplication">For application <span class="adoc-sub">(optional)</span></label>
                            <select class="form-select form-select-sm" name="application_id" id="docRejectApplication">
                                <option value="">Not specific to an application</option>
                                @foreach ($documentApplications as $appKey => $title)
                                    <option value="{{ $appKey }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-danger">Ask for a replacement</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="docRequestModal" tabindex="-1" aria-labelledby="docRequestTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content"
                  action="{{ route('applicant.documents.request', ['id' => $applicant->app_id]) }}">
                @csrf
                <input type="hidden" name="doc_type" id="docRequestType">
                <div class="modal-header">
                    <h1 class="modal-title fs-6" id="docRequestTitle">Request document</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="adoc-sub">The applicant sees this request on their Documents page.</p>
                    <div class="mb-3">
                        <label class="form-label" for="docRequestNote">Note to the applicant <span class="adoc-sub">(optional)</span></label>
                        <textarea class="form-control form-control-sm" name="note" id="docRequestNote" rows="3" maxlength="1000"></textarea>
                    </div>
                    @if ($processes->isNotEmpty())
                        {{-- Which application's document process a NEW request counts towards.
                             HR chooses; with several running there is no default. --}}
                        <div class="mb-3 js-process-choice">
                            <label class="form-label" for="docRequestProcess">Document deadline</label>
                            <select class="form-select form-select-sm js-process-select" name="process_id" id="docRequestProcess" @if ($processes->count() > 1) required @endif>
                                @if ($processes->count() > 1)
                                    <option value="" selected disabled>Choose an application…</option>
                                @endif
                                @foreach ($processes as $process)
                                    <option value="{{ $process->id }}">{{ $titleOf($process->application_id) }} — due {{ $process->deadline_at->format('M j, Y') }}</option>
                                @endforeach
                                <option value="none">No document deadline</option>
                            </select>
                        </div>
                    @endif
                    @if ($documentApplications->isNotEmpty())
                        <div class="js-application-context">
                            <label class="form-label" for="docRequestApplication">For application <span class="adoc-sub">(optional)</span></label>
                            <select class="form-select form-select-sm" name="application_id" id="docRequestApplication">
                                <option value="">Not specific to an application</option>
                                @foreach ($documentApplications as $appKey => $title)
                                    <option value="{{ $appKey }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary">Send request</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    $(function () {
        // Preview: PDFs in a frame, images as images. Cleared on close so a
        // closed modal is not still holding the file.
        $('.js-preview').on('click', function () {
            const src = $(this).data('src');
            const body = $('#docPreviewBody').empty();

            $('#docPreviewTitle').text($(this).data('title'));
            $('#docPreviewOpen').attr('href', src);

            if (Number($(this).data('pdf')) === 1) {
                body.append($('<iframe class="adoc-preview">').attr('src', src));
            } else {
                body.append($('<img class="adoc-preview-img" alt="">').attr('src', src));
            }
        });
        $('#docPreviewModal').on('hidden.bs.modal', function () { $('#docPreviewBody').empty(); });

        @if ($canReview)
            const REASONS = @json($reasonsByType);

            function syncReason() {
                const selected = $('#docRejectReason option:selected');
                $('#docRejectHint').text(selected.data('hint') || '');
                const noteRequired = Number(selected.data('noteRequired')) === 1;
                $('#docRejectNote').prop('required', noteRequired);
                $('#docRejectNoteOpt').text(noteRequired ? '(required)' : '(optional)');
            }

            $('.js-reject').on('click', function () {
                const select = $('#docRejectReason').empty();
                (REASONS[$(this).data('type')] || []).forEach(function (reason) {
                    select.append(
                        $('<option>').val(reason.code).text(reason.label)
                            .attr('data-hint', reason.hint).attr('data-note-required', reason.note_required ? 1 : 0)
                    );
                });

                $('#docRejectForm').attr('action', $(this).data('action'));
                $('#docRejectVersion').val($(this).data('version'));
                $('#docRejectTitle').text('Needs replacement — ' + $(this).data('title'));
                $('#docRejectNote').val('');
                syncReason();
            });

            $('#docRejectReason').on('change', syncReason);

            // The process picker only matters when a NEW request is created. An
            // existing request keeps the process it already belongs to. The
            // optional application context only applies to a request that is
            // not part of any document process.
            function syncProcessChoice(form) {
                const select = form.find('.js-process-select');
                const choice = form.find('.js-process-choice');
                const context = form.find('.js-application-context');

                // The picker's OWN display, not :hidden — this runs as the dialog
                // is being opened, when everything inside it still counts as
                // hidden. Only the replacement dialog hides the picker itself.
                if (!select.length || choice[0].style.display === 'none') {
                    context.show();
                    return;
                }

                const none = select.val() === 'none';
                context.toggle(none);
                if (!none) context.find('select').val('');
            }

            $('.js-process-select').on('change', function () {
                syncProcessChoice($(this).closest('form'));
            });

            $('.js-reject').on('click', function () {
                const form = $('#docRejectForm');
                const keepsRequest = Number($(this).data('hasRequest')) === 1;
                form.find('.js-process-choice').toggle(!keepsRequest);
                form.find('.js-process-select').prop('disabled', keepsRequest);
                syncProcessChoice(form);
            });

            $('.js-request').on('click', function () {
                syncProcessChoice($('#docRequestModal form'));
            });

            $('.js-change-deadline').on('click', function () {
                $('#processDeadlineForm').attr('action', $(this).data('action'));
                $('#modalProcessDeadlineTitle').text('Change deadline — ' + $(this).data('title'));
                $('#processDeadlineChange').val($(this).data('days'));
            });

            $('.js-request').on('click', function () {
                $('#docRequestType').val($(this).data('type'));
                $('#docRequestTitle').text('Request — ' + $(this).data('title'));
                $('#docRequestNote').val('');
            });
        @endif
    });
</script>
@endsection
