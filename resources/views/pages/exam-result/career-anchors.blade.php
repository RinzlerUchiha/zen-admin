<table class="table text-center mb-5">
    <tr>
        @foreach ($careerAnchorResult['category'] as $i => $item)
            <th>{{ $i }}</th>
        @endforeach
    </tr>
    <tr>
        @foreach ($careerAnchorResult['category'] as $i => $item)
            <td>{{ $item->sum()/5 }}</td>
        @endforeach
    </tr>
</table>
<table class="table text-center">
    <tr>
        <th>Never True for Me</th>
        <th colspan="2">Occasionally True for Me</th>
        <th colspan="2">Often True for Me</th>
        <th>Always True for Me</th>
        <th rowspan="2"></th>
    </tr>
    <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
    </tr>
</table>
<table class="table" id="tbl-career-anchors">
    <thead>
        <tr>
            <th>Most True (+4)</th>
            <th>Rate</th>
            <th>Item</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($careerAnchorResult['answer'] as $i => $item)
            <tr data-item="{{ $i }}">
                <td class="fs-6 text-primary">
                    {!! $item['isHighest'] ? '<i class="bi bi-check-square-fill"></i>' : '' !!}
                </td>
                <td>
                    {{ $item['rate'] }}
                </td>
                <td>{{ $item['desc'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>