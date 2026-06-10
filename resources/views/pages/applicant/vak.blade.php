@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-vak {
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
                    @if (!empty($vakResult))
                        @includeIf("pages.exam-result.vak")
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-auto mb-5">
            <div id="form-vak" class="card" oncontextmenu="return false;">
                <div class="card-body">
                    @foreach ($applicant->vak?->vak_ans ?? [] as $i => $item)
                        <p>{{ $answerList[$i]['question'] ?? '' }}<br>&emsp;{{ $answerList[$i]['answer'][$item] ?? '' }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@stop