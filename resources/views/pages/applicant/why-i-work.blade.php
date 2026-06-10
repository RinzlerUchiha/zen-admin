@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #exam-result {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 12px;
        }
    </style>

    <div id="exam-result" class="card ms-md-5 mb-5">
        <div class="card-body">
            <h5 class="card-title">Result</h5>
            @if (!empty($whyIWorkResult))
                @includeIf("pages.exam-result.why-i-work")
            @endif
        </div>
    </div>

@stop