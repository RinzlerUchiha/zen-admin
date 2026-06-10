{{-- <div class="card text-bg-body mb-3">
    <div class="card-body p-2">
        <h6 class="card-title mb-0">On-Leave/Offset</h6> --}}
        <table class="table table-sm mb-0">
            <thead class="table-light position-sticky top-0 shadow-sm">
                <tr>
                    <th></th>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Start</th>
                    <th>Return</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($list as $item)
                    @foreach ($item as $l)
                        <tr>
                            <td><img src="{{ $l->pic ?: asset('img/coffi.png') }}" class="img-thumbnail rounded" alt="{{ $l->la_empno ?? $l->os_empno }}" style="width: 50px;"></td>
                            <td>
                                <p class="mb-0">{{ $l->empname }}</p>
                                @if ($l->ongoing)
                                    <span class="badge text-bg-danger mx-auto">Ongoing</span>
                                @elseif ($l->soon)
                                    <span class="badge text-bg-warning mx-auto">Soon</span>
                                @endif
                            </td>
                            <td>{{ $l->timeoff_type }}</td>
                            <td class="text-nowrap">{{ date('M d Y', strtotime($l->timeoff_type == 'Offset' ? $v->os_offsetdt : $l->la_start)) }}</td>
                            <td class="text-nowrap">{{ $l->timeoff_type != 'Offset' ? date('M d Y', strtotime($l->la_return)) : '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    {{-- </div> --}}
{{-- </div> --}}
<script>
    let timeoffCnt = $('#timeoff-area table tbody tr').length;
    $('#timeoff-area').parent().find('.card-title').append(timeoffCnt ? ' (' + timeoffCnt + ')' : '');
</script>