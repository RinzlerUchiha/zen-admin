@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #form-abstract-reasoning {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
        font-size: 20px;
    }

    #form-abstract-reasoning img[data-item] {
        height: 70px;
    }

    #form-abstract-reasoning img {
        height: 50px;
    }
</style>

<div class="w-100 h-100">
    <div class="mb-3">Score: {{ $abstractReasoningResult ?? '' }}</div>
    <div id="form-abstract-reasoning" class="ms-md-5 mb-5" oncontextmenu="return false;">
        <div class="text-muted small mb-3">
            BASIC ABSTRACT REASONING (10 Questions; 10mins exam)
        </div>
        @foreach ($answerList ?? [] as $i => $item)
            <div class="mb-5">
                <div class="d-flex mb-2">
                    <span class="me-3">{{ $i }}</span><img src="{{ $item['question'] }}" class="img-fluid" id="q-{{ $i }}" data-item="{{ $i }}">
                </div>
                <div class="d-flex ps-5">
                    @if (!empty($applicant?->basicAbstractReasoning?->abstract_ans[$i]))
                        {!! $applicant?->basicAbstractReasoning?->abstract_ans[$i] == $item['answer'] ? '<i class="bi bi-check-lg text-success"></i>' : '<i class="bi bi-x-lg text-danger"></i>' !!}<span class="ms-3">{{ $applicant?->basicAbstractReasoning?->abstract_ans[$i] }}</span><img src="{{ !empty($item['option'][$applicant?->basicAbstractReasoning?->abstract_ans[$i]]) ? $item['option'][$applicant?->basicAbstractReasoning?->abstract_ans[$i]] : '' }}" class="ms-3 img-fluid rounded">
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@stop