@extends('pages.applicant.profile')

@section('profile_content')
    @include('pages.applicant.partials.assessment-summary')

    @if ($assessmentSummary?->hasResult)

<style>
    #form-basic-math {
        user-select: none;
        font-size: 15px;
    }
</style>

<div class="w-100 h-100">
    <div id="form-basic-math" class="ms-md-5 mb-5" oncontextmenu="return false;">
        <div class="text-muted small mb-3">BASIC MATH (12 Questions: 12mins exam)</div>
        @foreach ($answerList ?? [] as $i => $item)
            <div class="mb-5">
                <p class="mb-2">{{ $item['question'] }}</p>
                @if (!empty($applicant?->basicMath?->math_ans[$i]))
                    <p class="ms-3">{!! $applicant?->basicMath?->math_ans[$i] == $item['answer'] ? '<i class="bi bi-check-lg text-success"></i>' : '<i class="bi bi-x-lg text-danger"></i>' !!} {{ $applicant?->basicMath?->math_ans[$i] }} {{ !empty($item['option'][$applicant?->basicMath?->math_ans[$i]]) ? $item['option'][$applicant?->basicMath?->math_ans[$i]] : '' }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
    @endif
@stop