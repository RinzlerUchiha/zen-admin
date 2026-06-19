@extends('pages.applicant.profile')

@push('styles')
<style>
    #family-list-table {
        white-space: nowrap;
    }
</style>
@endpush

@section('profile_content')

<div class="table-responsive" id="family-list">
    <table class="table table-sm table-striped table-hover" id="family-list-table">
        <caption class="visually-hidden">Family Members</caption>
        <thead>
            <tr>
                <th scope="col">Relationship</th>
                <th scope="col">Last Name</th>
                <th scope="col">First Name</th>
                <th scope="col">Middle Name</th>
                <th scope="col">Suffix</th>
                <th scope="col">Maiden Name</th>
                <th scope="col">Birth Date</th>
                <th scope="col">Age</th>
                <th scope="col">Sex</th>
                <th scope="col">Contact #</th>
                <th scope="col">Address</th>
                <th scope="col">Occupation</th>
                <th scope="col">Work Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($applicant?->family ?? []) as $list)
            <tr>
                <td>{{ $list->fam_relationship }}</td>
                <td>{{ $list->fam_lastname }}</td>
                <td>{{ $list->fam_firstname }}</td>
                <td>{{ $list->fam_midname ?? '—' }}</td>
                <td>{{ $list->fam_suffix ?? '—' }}</td>
                <td>{{ $list->fam_maidenname ?? '—' }}</td>
                <td>{{ $list->fam_birthdate ? \Carbon\Carbon::parse($list->fam_birthdate)->format('M d, Y') : '—' }}</td>
                <td>{{ $list->age ?? '—' }}</td>
                <td>{{ $list->fam_sex }}</td>
                <td>{{ $list->fam_contact ?? '—' }}</td>
                <td>{{ $list->fam_add ?? '—' }}</td>
                <td>{{ $list->fam_occupation ?? '—' }}</td>
                <td>{{ $list->fam_workplace ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center text-muted py-3">No family members on record.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@stop