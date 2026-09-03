<table class="table table-sm table-bordered table-hover table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th style="width:24px;"></th>
            <th>MR No.</th>
            <th>Date Prepared</th>
            <th class="text-start">Requestor</th>
            <th class="text-start">Department</th>
            <th>Status</th>
            <th>Positions</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $v)
        <tr class="mpr-row-toggle" data-id="{{ $v->id }}" style="cursor:pointer;">
                <td class="text-center"><i class="fa fa-chevron-right mpr-toggle-icon"></i></td>
                <td class="text-nowrap">{{ $v->mr_no }}</td>
                <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($v->created_at)->format('M d, Y') }}</td>
                <td class="text-start">{{ $v->requestor_name }}</td>
                <td class="text-start">{{ $v->requestor_dept }}</td>
                <td>
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
                    <span class="mpv-chip {{ $statusClass }}">{{ $v->status }}</span>
                </td>
                <td>{{ $v->position_count }}</td>
                <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-info"
                        data-bs-toggle="modal" data-bs-target="#modal-mpr-view"
                        data-id="{{ $v->id }}"
                        onclick="event.stopPropagation();">
                        <i class="fa fa-eye"></i>
                    </button>
                </td>
            </tr>
            <template id="mpr-positions-{{ $v->id }}">
                <div class="p-2 bg-light">
                    @if ($v->positions->isEmpty())
                        <p class="text-muted small mb-0 ps-2">No positions on this request.</p>
                    @else
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr class="text-muted small">
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
                                    <td>{{ ucfirst($p->type) }}</td>
                                    <td>{{ $p->headcount }}</td>
                                    <td>{{ $p->nonnegotiable ?: '—' }}</td>
                                    <td>{{ $p->filled ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </template>
        @endforeach
    </tbody>
</table>