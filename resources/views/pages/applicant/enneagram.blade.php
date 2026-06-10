@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-enneagram {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 12px;
        }

        #enneagramTabContent ul li {
            text-decoration: none;
        }
    </style>


    <div id="exam-result" class="ms-md-5 mb-5 card">
        <div class="card-body">
            <h5 class="card-title">Result</h5>
            @if (!empty($topItems))
                @includeIf("pages.exam-result.enneagram")
            @endif
        </div>
    </div>

    <div id="form-enneagram" class="ms-md-5 mb-5" oncontextmenu="return false;">
        <div class="card-body">
            {{-- <div class="text-muted small mb-3">Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</div> --}}
            @foreach ($applicant?->enneagram?->enneagram_ans ?? [] as $s => $set)
                <h5 class="text-muted">#{{ $s }}</h5>

                @foreach ($set as $i)
                    <p>{{ "($i) " . $answerList[$s][$i] }}</p>
                @endforeach

                @if (!$loop->last)
                    <hr>
                @endif
            @endforeach
        </div>
    </div>

@stop
