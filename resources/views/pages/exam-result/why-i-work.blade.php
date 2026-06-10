<table class="table">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Category</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($whyIWorkResult as $item)
        <tr>
                <td>{{ $item['rank'] }}</td>
                <td>{{ $item['cat'] }}</td>
                <td>{{ $item['desc'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>