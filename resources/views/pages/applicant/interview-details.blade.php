@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #interview-details {
        font-size: 12px;
    }
</style>

<div id="interview-details" class="container-fluid">

    <div class="row mb-3 align-items-start">
        <div class="col-lg-auto">
            <span class="d-block mb-1">Interview Type</span>
            <div class="btn-group" role="group">
                @foreach(['Initial', '2nd Prelim', 'Final'] as $type)
                    <button type="button"
                        class="btn btn-sm {{ ($interviewDetail?->interview_type == $type) ? 'btn-primary' : 'btn-outline-secondary' }}"
                        disabled>
                        {{ $type }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="col-lg ms-auto text-end">
            <span class="d-block mb-1">Verdict</span>
            <div class="btn-group" role="group">
                @foreach(['Hired', 'Not Hired'] as $verdict)
                    <button type="button"
                        class="btn btn-sm {{ ($interviewDetail?->verdict == $verdict) ? ($verdict == 'Hired' ? 'btn-success' : 'btn-danger') : 'btn-outline-secondary' }}"
                        disabled>
                        {{ $verdict }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <hr>

    <div class="row g-3">
        <div class="col-lg-3">
            <span>Interviewer</span>
            <p class="fw-bold">{{ $interviewDetail?->interviewer_name ?? '—' }}</p>
        </div>
        <div class="col-lg-3">
            <span>Interviewed Date</span>
            <p class="fw-bold">{{ $interviewDetail?->interview_date ?? '—' }}</p>
        </div>
        <div class="col-lg-3">
            <span>Company</span>
            <p class="fw-bold">{{ $interviewDetail?->company ?? '—' }}</p>
        </div>
        <div class="col-lg-3">
            <span>Department</span>
            <p class="fw-bold">{{ $interviewDetail?->department ?? '—' }}</p>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <span>Interviewer's Remarks</span>
            <p class="fw-bold border rounded p-2" style="min-height: 100px; white-space: pre-wrap;">{{ $interviewDetail?->remarks ?? '—' }}</p>
        </div>
    </div>

</div>

@stop