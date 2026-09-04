@if ($data->isEmpty())
    <div class="mpr-empty-state">
        <i class="bi bi-inbox"></i>
        No {{ $stat }} manpower requests.
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
        <div class="mpr-card" data-id="{{ $v->id }}">
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

                <div></div>

                <div><span class="mpv-chip {{ $statusClass }}">{{ $v->status }}</span></div>

                <div class="mpr-positions-count">
                    {{ $v->position_count }}
                    <small>{{ Str::plural('position', $v->position_count) }}</small>
                </div>

                <div></div>

                <button type="button" class="mpr-view-btn"
                    data-bs-toggle="modal" data-bs-target="#modal-mpr-view"
                    data-id="{{ $v->id }}"
                    onclick="event.stopPropagation();" title="View details">
                    <i class="fa fa-eye"></i>
                </button>
            </div>

            <div class="mpr-detail-wrap"></div>
        </div>
    @endforeach
</div>
@endif

{{-- Positions detail templates, read via document.getElementById() by the toggle script above --}}
@foreach ($data as $v)
<template id="mpr-positions-{{ $v->id }}">
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
                    <tr>
                        <td>{{ $p->position_title }}</td>
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
    @endif
</template>
@endforeach