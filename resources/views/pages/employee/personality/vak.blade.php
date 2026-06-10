<style>
    #color-list {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }
</style>

<div id="color-list">
    <table class="table table-sm table-striped table-hover" id="color-list-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($empData as $list)
            <tr>
                <td class="text-nowrap">{{ $list->vak_dt }}</td>
                <td class="text-nowrap">{{ $list->result }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>