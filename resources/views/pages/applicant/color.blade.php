@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-color {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 12px;
        }
    </style>

    <div class="row ms-md-5">
        <div class="col-md mb-5">
            <div id="exam-result" class="card">
                <div class="card-body">
                    <h5 class="card-title">Result</h5>
                    @if (!empty($colorResult))
                        @includeIf("pages.exam-result.color")
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-auto mb-5">
            <div id="form-color" class="card" oncontextmenu="return false;">
                <div class="card-body">
                    {{-- <div class="text-muted small mb-3">Instructions: Choose the characteristic that best describes you: choose one answer per number.</div> --}}
                    @foreach ($applicant->color?->wcay_ans ?? [] as $i => $item)
                        <p>{{ $answerList[$i][$item] ?? '' }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@stop