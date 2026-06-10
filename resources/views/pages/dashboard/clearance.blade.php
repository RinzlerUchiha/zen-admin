{{-- <div class="card text-bg-body mb-3"> --}}
    {{-- <div class="card-body p-2"> --}}
        {{-- <h6 class="card-title pb-1 border-bottom">Clearance</h6> --}}
        <table class="table table-sm mb-0">
            <thead class="table-light position-sticky top-0 shadow-sm">
                <tr>
                    <th>Employee</th>
                    <th>Last Day</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($list as $item)
                    <tr class="cursor-pointer small" onclick="window.open('/clearance/?clr={{ $item->ecf_id }}', '_blank')">
                        <td>{{ ucwords($item->ecf_name) }}</td>
                        <td>{{ ucwords($item->ecf_lastday) }}</td>
                        <td class="text-center">{{ implode('/', [$item->cat_clr, $item->cat_cnt]) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    {{-- </div> --}}
{{-- </div> --}}
<script>
    let clrCnt = $('#clearance-area table tbody tr').length;
    $('#clearance-area').parent().find('.card-title').append(clrCnt ? ' (' + clrCnt + ')' : '');
</script>