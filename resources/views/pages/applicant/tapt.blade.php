@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-tapt {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 12px;
        }
    </style>

    <div id="exam-result" class="ms-md-5 mb-5 card">
        <div class="card-body">
            <h5 class="card-title">Result</h5>
            @if (!empty($taptResult))
                @includeIf("pages.exam-result.tapt")
            @endif
        </div>
    </div>

    <div id="form-tapt" class="mx-md-5 mb-5" oncontextmenu="return false;">
        {{-- <div class="text-muted small mb-3">Instructions: Below are four set of word pairs. Review each pair carefully. Choose the ONE word in each pair which most accurately describes the “real you” by putting a check mark before each word. Remember that there are no right or wrong responses</div> --}}
        <div class="row g-3">
            @foreach ($answerList ?? [] as $s => $set)
                <div class="col-md-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-evenly gap-3">
                            @foreach ($set as $c => $cat)
                                @if ($cat)
                                    <ul class="list-group w-100">
                                        <li class="list-group-item text-center fw-bold fs-5">{{ strtoupper($c) }}</li>
                                        @foreach ($cat as $i => $item)
                                            <li class="list-group-item">{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <br>
        <small class="text-muted mb-3">This exam was adopted from Paul D. Tiger & Barbara Barron-Tieger’s book on Do What You Are. Copyright 1992</small>
    </div>

@stop
