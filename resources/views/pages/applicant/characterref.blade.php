@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #characterref-list {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }
</style>
<div id="characterref-list">
    <table class="table table-sm table-striped table-hover" id="characterref-list-table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Company</th>
                <th>Address</th>
                <th>Position</th>
                <th>Contact</th>
                <th>Relationship</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->characterRef ?? []) as $list)
            <tr>
                <td class="text-nowrap">{{ $list->ref_fullname }}</td>
                <td class="text-nowrap">{{ $list->ref_company }}</td>
                <td class="text-nowrap">{{ $list->ref_address }}</td>
                <td class="text-nowrap">{{ $list->ref_position }}</td>
                <td class="text-nowrap">{{ $list->ref_contact }}</td>
                <td class="text-nowrap">{{ $list->ref_relationship }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop