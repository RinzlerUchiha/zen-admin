@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-miq {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 12px;
        }
    </style>

    <div id="exam-result" class="ms-md-5 mb-5 card">
        <div class="card-body">
            <h5 class="card-title">Result</h5>
            @if (!empty($miqResult))
                @includeIf("pages.exam-result.miq")
            @endif
        </div>
    </div>

    <div id="form-miq" class="ms-md-5 mb-5 card" oncontextmenu="return false;">
        <div class="card-body">
            {{-- <div class="text-muted small mb-3">Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</div> --}}
            @foreach ($applicant->miq?->miq_ans ?? [] as $item)
                <p>{{ $answerList[$item]['ans'] ?? '' }}</p>
            @endforeach
        </div>
    </div>

@stop