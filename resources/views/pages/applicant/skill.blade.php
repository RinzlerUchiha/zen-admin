@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #skills-list {
        min-width: 50vw;
        width: fit-content;
        font-size: 12px;
    }
</style>
<div id="skills-list">
    <table class="table table-sm table-striped table-hover">
        <thead>
            <tr>
                <th>Category</th>
                <th>Skills</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->skill ?? []) as $list)
            <tr>
                <td>{{ $list->sc_title }}</td>
                <td>{{ $list->skill_category == 7 ? $list->skill_others : $list->skill_name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop