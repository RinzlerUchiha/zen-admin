@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #employment-list {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }
</style>
<div id="employment-list">
    <table class="table table-sm table-striped table-hover" id="employment-list-table">
        <thead>
            <tr>
                <th>Company</th>
                <th>Address</th>
                <th>Position</th>
                <th>Supervisor</th>
                <th>Contact</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reason for Leaving</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->employmentRec ?? []) as $list)
            <tr>
                <td class="text-nowrap">{{ $list->empl_company }}</td>
                <td class="text-nowrap">{{ $list->empl_address }}</td>
                <td class="text-nowrap">{{ $list->empl_position }}</td>
                <td class="text-nowrap">{{ $list->empl_supervisor }}</td>
                <td class="text-nowrap">{{ $list->empl_contact }}</td>
                <td class="text-nowrap">{{ $list->empl_from }}</td>
                <td class="text-nowrap">{{ $list->empl_to }}</td>
                <td class="text-nowrap">{{ $list->empl_reason }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop