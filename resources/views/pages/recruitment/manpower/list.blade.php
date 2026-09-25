@if ($data->isEmpty())
    <div class="mpr-empty-state">
        <i class="bi bi-inbox"></i>
        @if ($stat === 'change-pending')
            No approved request is waiting on an edit or cancel decision.
        @else
            No {{ $stat }} manpower requests.
        @endif
    </div>
@else
<div class="mpr-card-list">
    @foreach ($data as $v)
        @php
            $statusClass = match ($v->status) {
                'Draft' => 'mpv-chip-draft',
                'Pending' => 'mpv-chip-pending',
                'Approved' => 'mpv-chip-approved',
                'Returned' => 'mpv-chip-returned',
                'Rejected' => 'mpv-chip-rejected',
                'Cancelled' => 'mpv-chip-cancelled',
                default => 'mpv-chip-draft',
            };
        @endphp
        @php $pendingChange = $v->pendingChange; @endphp
        <div class="mpr-card {{ $pendingChange ? 'mpr-card-flagged' : '' }}" data-id="{{ $v->id }}">
            <div class="mpr-card-row">
                <span class="mpr-toggle-btn"><i class="fa fa-chevron-right"></i></span>

                <div>
                    <div class="mpr-mrno">{{ $v->mr_no }}</div>
                    <div class="mpr-date">{{ \Illuminate\Support\Carbon::parse($v->created_at)->format('M d, Y') }}</div>
                </div>

                <div>
                    <div class="mpr-requestor">{{ $v->requestor_name }}</div>
                    <div class="mpr-dept">{{ $v->requestor_dept }}</div>
                </div>

                <div>
                    @if ($pendingChange)
                        <span class="mpr-change-chip" title="Asked on {{ \Illuminate\Support\Carbon::parse($pendingChange->created_at)->format('M d, Y h:i A') }}">
                            <i class="fa fa-clock"></i>
                            {{ $pendingChange->change_type === 'cancel' ? 'Cancel requested' : 'Edit requested' }}
                        </span>
                    @endif
                </div>

                <div><span class="mpv-chip {{ $statusClass }}">{{ $v->status }}</span></div>

                <div class="mpr-positions-count">
                    {{ $v->position_count }}
                    <small>{{ Str::plural('position', $v->position_count) }}</small>
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
                    <th>Filled</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($v->positions as $p)
                    <tr class="mpr-pos-row" title="View full request details">
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