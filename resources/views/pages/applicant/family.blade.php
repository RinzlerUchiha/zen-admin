@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #family-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<div id="family-list">
    <table class="table table-sm table-striped table-hover" id="family-list-table">
        <thead>
            <tr>
                <th>Relationship</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Suffix</th>
                {{-- <th>Maiden Name</th> --}}
                <th>Birth Date</th>
                <th>Age</th>
                <th>Sex</th>
                <th>Contact #</th>
                <th>Address</th>
                <th>Occupation</th>
                <th>Work Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->family ?? []) as $list)
            <tr>
                <td class="text-nowrap">{{ $list->fam_relationship }}</td>
                <td class="text-nowrap">{{ $list->fam_lastname }}</td>
                <td class="text-nowrap">{{ $list->fam_firstname }}</td>
                <td class="text-nowrap">{{ $list->fam_midname }}</td>
                <td class="text-nowrap">{{ $list->fam_suffix }}</td>
                <td class="text-nowrap">{{ $list->fam_birthdate }}</td>
                <td>{{ $list->age }}</td>
                <td>{{ $list->fam_sex }}</td>
                <td class="text-nowrap">{{ $list->fam_contact }}</td>
                <td>{{ $list->fam_add }}</td>
                <td>{{ $list->fam_occupation }}</td>
                <td>{{ $list->fam_workplace }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop