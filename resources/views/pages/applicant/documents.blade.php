@extends('pages.applicant.profile')

@push('styles')
<style>
    #applicant-documents { font-size: 13px; }

    .adoc-summary {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        border: 1px solid #dee2e6; border-radius: 8px; padding: 12px 16px; margin-bottom: 14px; background: #fff;
    }
    .adoc-summary b { font-size: 14px; }

    .adoc-badge {
        display: inline-block; padding: 2px 10px; border-radius: 999px;
        font-size: 11.5px; font-weight: 600; white-space: nowrap;
    }
    .adoc-missing  { background: #F1EFE8; color: #444441; }
    .adoc-pending  { background: #E8F0FE; color: #1B4FB0; }
    .adoc-accepted { background: #E1F5EE; color: #085041; }
    .adoc-rejected { background: #FAECE7; color: #712B13; }
    .adoc-complete { background: #E1F5EE; color: #085041; }
    .adoc-incomplete { background: #FFF4E0; color: #7A4B00; }
    .adoc-terminal { background: #FAECE7; color: #712B13; }
    .adoc-pool { background: #EFEAF7; color: #4A2E7A; }

    /* The run: one deadline and one attempt allowance for every document
       requested, shown apart from the per-document table so the two counts are
       never read as belonging to a single row. */
    .adoc-process {
        border: 1px solid #dee2e6; border-left: 3px solid #1B4FB0; border-radius: 8px;
        padding: 12px 16px; margin-bottom: 14px; background: #fff;
    }
    .adoc-process.is-terminal { border-left-color: #712B13; }
    .adoc-process-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .adoc-process-facts { display: flex; gap: 22px; flex-wrap: wrap; margin-top: 8px; }
    .adoc-process-facts div { font-size: 12px; color: #6c757d; }
    .adoc-process-facts b { display: block; font-size: 13.5px; color: #212529; }

    #applicant-documents-table thead th {
        font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .05em;
        color: #6c757d; border-bottom: 1px solid #dee2e6; white-space: nowrap;
    }
    #applicant-documents-table td { vertical-align: top; padding-top: 12px; padding-bottom: 12px; }
    .adoc-sub { color: #6c757d; font-size: 12px; }
    .adoc-note { font-size: 12px; margin-top: 4px; color: #5b3a2c; }
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

    {{-- The document-completion run (HireFlow 2.5 · M3). One deadline and one
         attempt allowance for the whole set of requests. Adding a request does
         not move the deadline — only the button here does. --}}
    @php $process = $summary['process']; @endphp

    @if ($process && $process->is_active)
        <div class="adoc-process">
            <div class="adoc-process-head">
                <div>
                    <b>Document completion in progress</b>
                    <div class="adoc-sub">
                        Started {{ $process->started_at->format('M j, Y') }} by {{ $process->started_by }}
                        @if ($process->application_id && isset($documentApplications[$process->application_id]))
                            · {{ $documentApplications[$process->application_id] }}
                        @endif
                    </div>
                </div>
                @if ($canReview)
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-process">
                        Change deadline or attempts
                    </button>
                @endif
            </div>
            <div class="adoc-process-facts">
                <div>
                    <b>{{ $process->deadline_at->format('M j, Y') }}</b>
                    Deadline · {{ $process->deadline_days }} days, holidays excluded
                </div>
                <div>
                    <b>{{ $process->attempts_used }} of {{ $process->max_attempts }}</b>
                    Attempts used across all documents
                </div>
                <div>
                    <b>{{ $process->deadline_at->isPast() ? 'Overdue' : $process->deadline_at->diffInDays(now()) . ' days left' }}</b>
                    {{ $process->deadline_at->isPast() ? 'Closes as Non-Responsive on the next sweep' : 'Time remaining' }}
                </div>
            </div>
        </div>
    @elseif ($process)
        <div class="adoc-process is-terminal">
            <div class="adoc-process-head">
                <div>
                    <b>{{ $process->status_label }}</b>
                    <div class="adoc-sub">
                        {{ $process->outcome_at?->format('M j, Y') }}
                        @if ($process->closed_note) · {{ $process->closed_note }} @endif
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    @if ($process->in_candidate_pool)
                        <span class="adoc-badge adoc-pool">Candidate Pool</span>
                    @endif
                    @if ($canReview)
                        <form method="POST" action="{{ route('applicant.documents.completion.start', $applicant->app_id) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Start a new process</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="adoc-process-facts">
                <div><b>{{ $process->attempts_used }} of {{ $process->max_attempts }}</b> Attempts used</div>
                <div><b>{{ $process->deadline_at->format('M j, Y') }}</b> Deadline that applied</div>
            </div>
        </div>
    @elseif ($canReview)
        <div class="adoc-process">
            <div class="adoc-process-head">
                <div>
                    <b>No document completion process running</b>
                    <div class="adoc-sub">
                        Start one to give the applicant a single deadline and a shared attempt limit for
                        everything you request.
                    </div>
                </div>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-process">
                    Start document completion
                </button>
            </div>
        </div>
    @endif

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

    {{-- Starting the run, or changing what it allows. Both write the same two
         numbers; which route they go to is the only difference, because a
         deadline that already exists is only ever moved deliberately. --}}
    <div class="modal fade" id="modal-process" tabindex="-1" aria-labelledby="modalProcessTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content"
                  action="{{ $process && $process->is_active
                      ? route('applicant.documents.completion.update', ['id' => $applicant->app_id, 'process' => $process->id])
                      : route('applicant.documents.completion.start', $applicant->app_id) }}">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-6" id="modalProcessTitle">
                        {{ $process && $process->is_active ? 'Change deadline or attempts' : 'Start document completion' }}
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="adoc-sub">
                        One deadline and one attempt limit cover every document you request. Adding another
                        request later does not move the deadline.
                    </p>

                    <div class="mb-3">
                        <label class="form-label" for="processDeadlineDays">Days to complete</label>
                        <input type="number" class="form-control form-control-sm" name="deadline_days"
                               id="processDeadlineDays" min="1"
                               max="{{ config('applicant_documents.completion.deadline_days_max') }}"
                               value="{{ $process?->deadline_days ?? config('applicant_documents.completion.deadline_days') }}">
                        <div class="form-text">
                            Calendar days from today. Weekends count; Philippine public holidays do not.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="processMaxAttempts">Attempts allowed</label>
                        <input type="number" class="form-control form-control-sm" name="max_attempts"
                               id="processMaxAttempts" min="1"
                               max="{{ config('applicant_documents.completion.max_attempts_max') }}"
                               value="{{ $process?->max_attempts ?? config('applicant_documents.completion.max_attempts') }}">
                        <div class="form-text">
                            Shared across every document. Each rejection uses one; accepting uses none.
                            Running out ends the process as <b>Document Requirements Not Met</b>.
                        </div>
                    </div>

                    @unless ($process && $process->is_active)
                        @if ($documentApplications->isNotEmpty())
                            <div class="mb-1">
                                <label class="form-label" for="processApplication">For which application <span class="text-muted">(optional)</span></label>
                                <select class="form-select form-select-sm" name="application_id" id="processApplication">
                                    <option value="">Not tied to one application</option>
                                    @foreach ($documentApplications as $applicationId => $label)
                                        <option value="{{ $applicationId }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">The outcome is recorded against the application you pick.</div>
                            </div>
                        @endif
                    @endunless
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        {{ $process && $process->is_active ? 'Save' : 'Start' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                    @if ($documentApplications->isNotEmpty())
                        <div>
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
                    @if ($documentApplications->isNotEmpty())
                        <div>
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

            $('.js-request').on('click', function () {
                $('#docRequestType').val($(this).data('type'));
                $('#docRequestTitle').text('Request — ' + $(this).data('title'));
                $('#docRequestNote').val('');
            });
        @endif
    });
</script>
@endsection
