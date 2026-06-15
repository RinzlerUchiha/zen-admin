@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #interview-details {
        font-size: 12px;
    }
</style>

<script>
const interviewDetails = @json($interviewDetails);

function loadInterviewType(type) {
    const data = interviewDetails[type] || {};

    // set hidden field
    document.getElementById('interview_type').value = type;

    // highlight active button
    document.querySelectorAll('.btn-interview-type').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.type === type);
        btn.classList.toggle('btn-outline-primary', btn.dataset.type !== type);
    });

    // fill fields
    document.getElementById('interviewer_name').value = data.interviewer_name || '';
    document.getElementById('interview_date').value = data.interview_date || '';
    document.getElementById('company').value = data.company || '';
    document.getElementById('position').value = data.position || '';
    document.getElementById('remarks').value = data.remarks || '';

    // verdict
    document.querySelectorAll('input[name="verdict"]').forEach(r => {
        r.checked = r.value === (data.verdict || '');
    });
}

function toggleVerdict(radio) {
    if (radio.dataset.prev === 'true') {
        radio.checked = false;
        radio.dataset.prev = 'false';
    } else {
        document.querySelectorAll('input[name="verdict"]').forEach(r => r.dataset.prev = 'false');
        radio.dataset.prev = 'true';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    loadInterviewType('Initial');
        flatpickr('#interview_date', {
        dateFormat: 'Y-m-d',   // value submitted to Laravel
        altInput: true,
        altFormat: 'm-d-Y',    // value displayed to the user
        allowInput: true,
    });
});
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<div id="interview-details" class="container-fluid">

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form action="{{ route('applicant.interview.save', ['id' => $applicant?->app_id]) }}" method="POST" autocomplete="off">
    @csrf
    <input type="hidden" name="interview_type" id="interview_type" value="Initial">

    <div class="row mb-3 align-items-start">
        <div class="col-lg-auto">
            <span class="d-block mb-1">Interview Type</span>
            <div class="btn-group" role="group">
                @foreach(['Initial', '2nd Prelim', 'Final'] as $type)
                    <button type="button" class="btn btn-sm btn-interview-type btn-outline-primary" data-type="{{ $type }}" onclick="loadInterviewType('{{ $type }}')">{{ $type }}</button>
                @endforeach
            </div>
        </div>

        <div class="col-lg ms-auto text-end">
            <span class="d-block mb-1">Verdict</span>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="verdict" id="verdict_hired" value="Hired" data-prev="false" onclick="toggleVerdict(this)">
                <label class="btn btn-sm btn-outline-success" for="verdict_hired">Hired</label>

                <input type="radio" class="btn-check" name="verdict" id="verdict_nothired" value="Not Hired" data-prev="false" onclick="toggleVerdict(this)">
                <label class="btn btn-sm btn-outline-danger" for="verdict_nothired">Not Hired</label>
            </div>
        </div>
    </div>

    <hr>

    <div class="row g-3">
        <div class="col-lg-3">
            <label class="form-label">Interviewer Name</label>
            <input type="text" class="form-control form-control-sm" name="interviewer_name" id="interviewer_name">
        </div>
        <div class="col-lg-3">
                <label class="form-label">Interviewed Date</label>
                <input type="text" class="form-control form-control-sm" name="interview_date" id="interview_date" placeholder="Select date...">
            </div>
        <div class="col-lg-3">
            <label class="form-label">Company</label>
            <input type="text" class="form-control form-control-sm" name="company" id="company">
        </div>
        <div class="col-lg-3">
            <label class="form-label">Position Applying For</label>
            <input type="text" class="form-control form-control-sm" name="position" id="position">
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <label class="form-label">Interviewer's Remarks</label>
            <textarea class="form-control form-control-sm" name="remarks" id="remarks" rows="5"></textarea>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-auto">
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
    </div>

</form>
</div>

@stop