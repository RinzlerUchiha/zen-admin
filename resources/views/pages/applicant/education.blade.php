@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #education-list {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }
</style>

<div id="education-list">
    <table class="table table-sm table-striped table-hover mb-5" id="education-list-table">
        <thead>
            <tr>
                <th>Level</th>
                <th>School</th>
                <th>Address</th>
                <th>Degree Title</th>
                <th>Major</th>
                <th>Year Graduated</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->education ?? []) as $list)
            <tr>
                <td class="text-nowrap">{{ $list->educ_level }}</td>
                <td class="text-nowrap">{{ $list->educ_school }}</td>
                <td class="text-nowrap">{{ $list->educ_schooladd }}</td>
                <td>{{ $list->educ_degreetitle }}</td>
                <td>{{ $list->educ_major }}</td>
                <td class="text-nowrap">{{ $list->educ_yeargrad }}</td>
                <td class="text-nowrap">{{ $list->educ_currStatus }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop