@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #license-list {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }
</style>
<div id="license-list">
    <table class="table table-sm table-striped table-hover" id="license-list-table">
        <thead>
            <tr>
                <th>License Type</th>
                <th>Registration Date</th>
                <th>Valid Until</th>
                <th>Profession</th>
                <th>Attachment</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->license ?? []) as $list)
            <tr>
                <td class="text-nowrap">{{ $list->el_type }}</td>
                <td class="text-nowrap">{{ $list->el_regdate }}</td>
                <td class="text-nowrap">{{ $list->el_expdate }}</td>
                <td class="text-nowrap">{{ $list->el_profession }}</td>
                <td>
                    @if($list->el_file)
                        <embed src="{{ '/file/get/license/'.$list->el_file }}" style="max-width: 100%; height: 150px;">
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop