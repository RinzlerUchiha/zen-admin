@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-disc {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 12px;
        }
    </style>

    <div id="exam-result" class="ms-md-5 mb-5 card">
        <div class="card-body">
            <h5 class="card-title">Result</h5>
            @if (!empty($discResult))
                @includeIf("pages.exam-result.disc")
            @endif
        </div>
    </div>

    <div id="form-disc" class="mx-md-5 mb-5" oncontextmenu="return false;">
        {{-- <div class="text-muted small mb-3">Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</div> --}}
            <table class="table" id="tbl-disc">
                <tbody>
                    @foreach ($answerList ?? [] as $s => $set)
                        <tr>
                            <th>Rank</th>
                            <th>Set {{ $s }}</th>
                        </tr>
                        @foreach ($set as $i => $item)
                            <tr data-set="{{ $s }}" data-item="{{ $i }}" data-content="{{ $item }}">
                                <td class="rank-area">
                                    <div class="rank" data-rank="{{ $applicant?->disc?->disc_ans[$s][$i] ?? $loop->iteration }}">{{ $applicant?->disc?->disc_ans[$s][$i] ?? $loop->iteration }}</div>
                                </td>
                                <td>{{ $item }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
    </div>

@stop
