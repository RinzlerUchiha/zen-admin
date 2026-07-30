<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th>Date Prepared</th>
            <th>Requested By</th>
            <th>Department</th>
            @if($stat == 'declined')
                <th>Reason</th>
            @endif
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $v)
            <tr data-id="{{ $v->mp_id }}" style="cursor:pointer;" onclick="viewManpowerRequest({{ $v->mp_id }})">
                <td>{{ $v->mp_dtprepared }}</td>
                <td>{{ $v->requestor_name }}</td>
                <td>{{ $v->requestor_dept }}</td>
                @if($stat == 'declined')
                    <td>{{ $v->mp_decline_reason }}</td>
                @endif
                <td>
                    @if($v->mp_requestby == $user_empno && in_array($stat, ['draft']))
                        <button type="button" class="btn btn-sm btn-danger" onclick="event.stopPropagation(); removeManpowerRequest({{ $v->mp_id }})">
                            <i class="fa fa-trash"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No requests found.</td></tr>
        @endforelse
    </tbody>
</table>