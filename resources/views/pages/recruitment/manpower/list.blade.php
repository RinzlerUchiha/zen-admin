<table class="table table-sm table-bordered table-hover table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>Date Prepared</th>
            <th class="text-start">Requestor</th>
            <th class="text-start">Department</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $v)
            <tr>
                <td class="text-nowrap">{{ $v->mp_dtprepared }}</td>
                <td class="text-start">{{ $v->requestor_name }}</td>
                <td class="text-start">{{ $v->requestor_dept }}</td>
                <td>{{ ucfirst($v->mp_status) }}</td>
                <td class="text-nowrap">
                    @if ($v->mp_status == 'draft')
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#modal-mpr-form"
                            data-id="{{ $v->mp_id }}">
                            <i class="fa fa-edit"></i>
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-info"
                            data-bs-toggle="modal" data-bs-target="#modal-mpr-view"
                            data-id="{{ $v->mp_id }}">
                            <i class="fa fa-eye"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>