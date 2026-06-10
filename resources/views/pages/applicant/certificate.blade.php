@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #certificate-list {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }
</style>
<div id="certificate-list">
    <table class="table table-sm table-striped table-hover" id="certificate-list-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Completion Date</th>
                <th>Location of Event/Course</th>
                <th>Speaker</th>
                <th>Attachment</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($applicant?->certificate ?? []) as $list)
            <tr>
                <td class="text-nowrap">{{ $list->cert_title }}</td>
                <td class="text-nowrap">{{ $list->cert_date }}</td>
                <td class="text-nowrap">{{ $list->cert_address }}</td>
                <td class="text-nowrap">{{ $list->cert_speaker }}</td>
                <td>
                    @if($list->cert_file)
                        <embed src="{{ '/file/get/certificate/'.$list->cert_file }}" style="max-width: 100%; height: 150px;">
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop