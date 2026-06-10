<table class="table table-sm mb-0">
    <thead class="table-light position-sticky top-0 shadow-sm">
        <tr>
            <th></th>
            <th>Employee</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($list as $item)
            @foreach ($item as $l)
                <tr>
                    <td><img src="{{ $l->pic ?: asset('img/coffi.png') }}" class="img-thumbnail rounded" alt="{{ $l->emp_no }}" style="width: 50px;"></td>
                    <td>
                        <p class="mb-0">{{ $l->empname }}</p>
                        @if ($l->ongoing)
                            <span class="badge text-bg-danger mx-auto">Ongoing</span>
                        @elseif ($l->soon)
                            <span class="badge text-bg-warning mx-auto">Soon</span>
                        @endif
                    </td>
                    <td class="text-nowrap">{{ date('M d Y', strtotime($l->date_dtr)) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
<script>
    let travelCnt = $('#travel-area table tbody tr').length;
    $('#travel-area').parent().find('.card-title').append(travelCnt ? ' (' + travelCnt + ')' : '');
</script>