@extends('pages.applicant.profile')

@section('profile_content')

<style>
    #interview-details {
        font-size: 12px;
    }

    /* Quill editor container */
    .ql-container {
        font-size: 12px;
        font-family: inherit;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .ql-toolbar {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background: #f8f9fa;
    }

    .ql-editor {
        min-height: 120px;
        max-height: 300px;
        overflow-y: auto;
    }

    /* Match Bootstrap's form-control look */
    .quill-wrapper .ql-toolbar,
    .quill-wrapper .ql-container {
        border-color: #dee2e6;
    }

    .quill-wrapper .ql-toolbar:focus-within,
    .quill-wrapper .ql-container:focus-within {
        border-color: #86b7fe;
    }
</style>

{{-- Quill CSS --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">

<script>
const interviewDetails = @json($interviewDetails);
let datePicker = null;
let pageJustLoaded = true;

function loadInterviewType(type) {
    const data = interviewDetails[type] || {};

    // set hidden field
    document.getElementById('interview_type').value = type;

    // hide success alert when switching tabs manually
    if (!pageJustLoaded) {
        const alert = document.getElementById('save-alert');
        if (alert) alert.style.display = 'none';
    }

    // highlight active button
    document.querySelectorAll('.btn-interview-type').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.type === type);
        btn.classList.toggle('btn-outline-primary', btn.dataset.type !== type);
    });

    // fill plain fields
    document.getElementById('interviewer_name').value = data.interviewer_name || '';
    if (datePicker) {
        datePicker.setDate(data.interview_date || '', false);
    }
    document.getElementById('company').value = data.company || '';
    document.getElementById('position').value = data.position || '';

    // fill Quill editors
    // remarks: stored as HTML, set via clipboard API to preserve formatting
    if (window.quilRemarks) {
        if (data.remarks) {
            window.quilRemarks.clipboard.dangerouslyPasteHTML(data.remarks);
        } else {
            window.quilRemarks.setContents([]);
        }
    }

    // recommendation
    if (window.quilRecommendation) {
        if (data.recommendation) {
            window.quilRecommendation.clipboard.dangerouslyPasteHTML(data.recommendation);
        } else {
            window.quilRecommendation.setContents([]);
        }
    }

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

    // ── Flatpickr ──────────────────────────────────────────────────
    datePicker = flatpickr('#interview_date', {
        dateFormat: 'Y-m-d',
        altInput:   true,
        altFormat:  'm-d-Y',
        allowInput: true,
    });

    // ── Quill toolbar definition (shared) ─────────────────────────
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'color': [] }, { 'background': [] }],
        ['clean']
    ];

    // ── Remarks editor ────────────────────────────────────────────
    window.quilRemarks = new Quill('#quill-remarks', {
        theme:   'snow',
        modules: { toolbar: toolbarOptions },
        placeholder: 'Enter interviewer\'s remarks here…',
    });

    // ── Recommendation editor ─────────────────────────────────────
    window.quilRecommendation = new Quill('#quill-recommendation', {
        theme:   'snow',
        modules: { toolbar: toolbarOptions },
        placeholder: 'Enter recommendation here…',
    });

    // ── Sync Quill → hidden textareas before form submit ──────────
    const form = document.getElementById('form-interview');
    form.addEventListener('submit', function () {
        document.getElementById('remarks').value =
            window.quilRemarks.root.innerHTML === '<p><br></p>'
                ? ''
                : window.quilRemarks.root.innerHTML;

        document.getElementById('recommendation').value =
            window.quilRecommendation.root.innerHTML === '<p><br></p>'
                ? ''
                : window.quilRecommendation.root.innerHTML;
    });

    // ── Load initial tab ──────────────────────────────────────────
    loadInterviewType('{{ session('active_type', 'Initial') }}');
    pageJustLoaded = false;
});
</script>

<link  rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- Quill JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>

<div id="interview-details" class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success" id="save-alert">{{ session('success') }}</div>
    @endif

    <form id="form-interview"
          action="{{ route('applicant.interview.save', ['id' => $applicant?->app_id]) }}"
          method="POST"
          autocomplete="off">
        @csrf
        <input type="hidden" name="interview_type" id="interview_type" value="Initial">

        {{-- ── Interview type & Verdict ───────────────────────────────── --}}
        <div class="row mb-3 align-items-start">
            <div class="col-lg-auto">
                <span class="d-block mb-1">Interview Type</span>
                <div class="btn-group" role="group">
                    @foreach(['Initial', '2nd Prelim', 'Final'] as $type)
                        <button type="button"
                                class="btn btn-sm btn-interview-type btn-outline-primary"
                                data-type="{{ $type }}"
                                onclick="loadInterviewType('{{ $type }}')">{{ $type }}</button>
                    @endforeach
                </div>
            </div>

            <div class="col-lg ms-auto text-end">
                <span class="d-block mb-1">Interviewer's Result</span>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="verdict" id="verdict_hired"
                           value="Hired" data-prev="false" onclick="toggleVerdict(this)">
                    <label class="btn btn-sm btn-outline-success" for="verdict_hired">Hired</label>

                    <input type="radio" class="btn-check" name="verdict" id="verdict_nothired"
                           value="Not Hired" data-prev="false" onclick="toggleVerdict(this)">
                    <label class="btn btn-sm btn-outline-danger" for="verdict_nothired">Not Hired</label>
                </div>
            </div>
        </div>

        <hr>

        {{-- ── Basic info fields ─────────────────────────────────────── --}}
        <div class="row g-3">
            <div class="col-lg-3">
                <label class="form-label">Interviewer Name</label>
                <input type="text" class="form-control form-control-sm"
                       name="interviewer_name" id="interviewer_name">
            </div>
            <div class="col-lg-3">
                <label class="form-label">Interviewed Date</label>
                <input type="text" class="form-control form-control-sm"
                       name="interview_date" id="interview_date" placeholder="Select date…">
            </div>
            <div class="col-lg-3">
                <label class="form-label">Company</label>
                <input type="text" class="form-control form-control-sm"
                       name="company" id="company">
            </div>
            <div class="col-lg-3">
                <label class="form-label">Position Applying For</label>
                <input type="text" class="form-control form-control-sm"
                       name="position" id="position">
            </div>
        </div>

        {{-- ── Rich-text: Remarks ────────────────────────────────────── --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <label class="form-label">Interviewer's Remarks</label>
                {{-- Hidden textarea that receives the HTML from Quill before submit --}}
                <textarea name="remarks" id="remarks" style="display:none;"></textarea>
                <div class="quill-wrapper">
                    <div id="quill-remarks"></div>
                </div>
            </div>

            {{-- ── Rich-text: Recommendation ────────────────────────── --}}
            <div class="col-lg-6">
                <label class="form-label">Recommendation</label>
                <textarea name="recommendation" id="recommendation" style="display:none;"></textarea>
                <div class="quill-wrapper">
                    <div id="quill-recommendation"></div>
                </div>
            </div>
        </div>

        {{-- ── Submit ────────────────────────────────────────────────── --}}
        <div class="row mt-3">
            <div class="col-lg-auto">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </div>

    </form>
</div>

@stop