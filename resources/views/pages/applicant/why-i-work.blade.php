@extends('pages.applicant.profile')

@section('profile_content')
    @include('pages.applicant.partials.assessment-summary')

    @if ($assessmentSummary?->hasResult)

    <style>
        #exam-result {
            user-select: none;
            font-size: var(--zn-fs-ui);
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

    @endif
@stop