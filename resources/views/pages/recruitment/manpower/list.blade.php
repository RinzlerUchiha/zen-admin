@if ($data->isEmpty())
    <div class="mpr-empty-state">
        <i class="bi bi-inbox"></i>
        @if ($stat === 'change-pending')
            No approved request is waiting on an edit or cancel decision.
        @else
            @if ($stat === 'served')
            No approved request has had a job posting created yet.
        @elseif ($stat === 'approved')
            No approved request is still waiting for its first job posting.
        @else
            No {{ ['update' => 'Update', 'declined' => 'Declined'][$stat] ?? ucfirst($stat) }} manpower requests.
        @endif
        @endif
    </div>
@else
<div class="mpr-card-list">
    @foreach ($data as $v)
        @php
            // Served is its own tab now, so naming it here is unambiguous and
            // tells HR at a glance that hiring has started on this request.
            $shownStatus = $v->is_served ? 'Served' : match ($v->status) {
                'Returned' => 'Update',
                'Rejected' => 'Declined',
                default => $v->status,
            };
            $statusClass = match ($shownStatus) {
                'Pending' => 'mpv-chip-pending',
                'Approved' => 'mpv-chip-approved',
                'Served' => 'mpv-chip-served',
                'Update' => 'mpv-chip-returned',
                'Declined' => 'mpv-chip-rejected',
                'Cancelled' => 'mpv-chip-cancelled',
                default => 'mpv-chip-draft',
            };

            // What the request is FOR leads the row: HR recognises the job,
            // not the reference number.
            $titles = $v->positions->pluck('position_title')->filter()->unique()->values();
            $lead = $titles->first() ?: 'No positions added yet';
            $more = max(0, $titles->count() - 1);

            $pendingChange = $v->pendingChange;
        @endphp
        <div class="mpr-card {{ $pendingChange ? 'mpr-card-flagged' : '' }}" data-id="{{ $v->id }}">
            <div class="mpr-card-row" role="button" tabindex="0" aria-expanded="false"
                aria-label="{{ $lead }}{{ $more ? ' and ' . $more . ' more' : '' }}, {{ $v->mr_no }} — {{ $shownStatus }}. Show positions.">
                <span class="mpr-toggle-btn"><i class="fa fa-chevron-right"></i></span>

                <div class="mpr-ident">
                    <div class="mpr-subject">
                        {{ $lead }}@if ($more)<span class="mpr-more">+{{ $more }} more</span>@endif
                    </div>
                    <div class="mpr-mrno">{{ $v->mr_no }}</div>
                    <div class="mpr-meta">
                        <span>{{ $v->requestor_dept }}</span>
                        <span>{{ $v->requestor_name }}</span>
                        <span>{{ $v->position_count }} {{ Str::plural('position', $v->position_count) }}</span>
                        <span>{{ $v->total_headcount }} headcount</span>
                        <span>{{ \Illuminate\Support\Carbon::parse($v->created_at)->format('M d') }}</span>
                    </div>
                </div>

                <div class="mpr-state">
                    <span class="mpv-chip {{ $statusClass }}">{{ $shownStatus }}</span>
                    @if ($v->status === 'Approved' && $v->position_count > 0)
                        <span class="mpr-post-progress {{ $v->posted_count === $v->position_count ? 'is-complete' : '' }}"
                            title="{{ $v->posted_count }} of {{ $v->position_count }} positions have a job posting">
                            {{ $v->posted_count }}/{{ $v->position_count }} posted
                        </span>
                    @endif
                    @if ($pendingChange)
                        <span class="mpr-change-chip" title="Asked on {{ \Illuminate\Support\Carbon::parse($pendingChange->created_at)->format('M d, Y h:i A') }}">
                            <i class="fa fa-clock"></i>
                            {{ $pendingChange->change_type === 'cancel' ? 'Cancel requested' : 'Edit requested' }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="mpr-detail-wrap"></div>
        </div>
    @endforeach
</div>
@endif

{{-- Positions detail templates, read via document.getElementById() by the toggle script above --}}
@foreach ($data as $v)
<template id="mpr-positions-{{ $v->id }}">
    @php $pendingChange = $v->pendingChange; @endphp
    @if ($pendingChange)
        <div class="mpr-change-note">
            <i class="fa fa-clock"></i>
            <div>
                <strong>{{ $pendingChange->change_type === 'cancel' ? 'Cancel' : 'Edit' }} requested</strong>
                by {{ $v->requestor_name }}
                on {{ \Illuminate\Support\Carbon::parse($pendingChange->created_at)->format('M d, Y h:i A') }}.
                <div class="mpr-change-reason">
                    {{ trim((string) $pendingChange->reason) !== '' ? $pendingChange->reason : 'No reason given.' }}
                </div>
                <div class="mpr-change-where">
                    Waiting on the Requestor&rsquo;s Approver in HireFlow. Approve or decline it there.
                </div>
            </div>
        </div>
    @endif

    @if ($v->positions->isEmpty())
        <p class="text-muted small mb-0 ps-2">No positions on this request.</p>
    @else
    <div class="mpr-detail-card">
        <table class="mpr-detail-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Headcount</th>
                    <th>Non-Negotiable</th>
                    <th>Job posting</th>
                    <th>Filled</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($v->positions as $p)
                    <tr class="mpr-pos-row {{ $p->is_posted ? 'is-posted-row' : '' }}"
                        title="View full request details">
                        <td>
                            <button type="button" class="mpr-subject-btn"
                                data-bs-toggle="modal" data-bs-target="#modal-mpr-view"
                                data-id="{{ $v->id }}"
                                title="View full request details">
                                <span>{{ $p->position_title }}</span>
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </td>
                        <td>
                            @php $typeClass = strtolower($p->type) === 'additional' ? 'mpv-type-additional' : 'mpv-type-replacement'; @endphp
                            <span class="mpv-type-chip {{ $typeClass }}">{{ ucfirst($p->type) }}</span>
                        </td>
                        <td>{{ $p->headcount }}</td>
                        <td>{{ $p->nonnegotiable ?: '—' }}</td>
                        <td>
                            <span class="mpr-post-chip {{ $p->is_posted ? 'is-posted' : '' }}">
                                {{ $p->is_posted ? 'Posted' : 'Not posted' }}
                            </span>
                        </td>
                        <td>{{ $p->filled ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mpr-detail-hint">
        <i class="fa fa-info-circle"></i> Click a position row to open the full request details.
    </div>
    @endif
</template>
@endforeach