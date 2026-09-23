@extends('layouts.layout')

@section('content')
    {{-- HireFlow page: the shared HireFlow look, scoped to this wrapper. --}}
    @include('partials.hireflow-theme')
    <div class="hf-theme">

    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script> --}}

    <style>
        /* The applicant at a glance, above the tabs. */
        .ap-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            align-items: flex-start;
            justify-content: space-between;
            background: var(--zn-surface);
            border: 1px solid var(--zn-line);
            border-radius: var(--zn-radius-lg);
            padding: 14px 16px;
            margin-bottom: 14px;
        }

        .ap-overview-id {
            display: flex;
            gap: 12px;
            align-items: center;
            min-width: 240px;
        }

        .ap-overview-id img,
        .ap-overview-initials {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            flex: none;
        }

        .ap-overview-initials {
            display: grid;
            place-items: center;
            background: var(--zn-accent-soft);
            color: var(--zn-accent);
            font-weight: 700;
        }

        .ap-overview-id b {
            display: block;
            font-size: var(--zn-fs-lg);
        }

        .ap-overview-id span {
            display: block;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink-3);
        }

        .ap-overview-facts {
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
        }

        .ap-fact {
            min-width: 150px;
        }

        .ap-fact-label {
            display: block;
            font-size: var(--zn-fs-xs);
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--zn-ink-3);
            margin-bottom: 3px;
        }

        .ap-fact-value {
            display: block;
            font-size: var(--zn-fs-sm);
            color: var(--zn-ink);
        }

        .ap-fact-link {
            display: inline-block;
            margin-top: 4px;
            font-size: var(--zn-fs-sm);
            font-weight: 600;
        }

        .ap-applications {
            list-style: none;
            padding: 0;
            margin: 6px 0 0;
            font-size: var(--zn-fs-sm);
        }

        .ap-applications li {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            padding: 2px 0;
        }

        :root {
            --my-top-space: calc(var(--main-top-margin) + .25rem);
        }

        #sidebar {
            max-height: calc(100vh - var(--my-top-space));
            overflow: auto;
        }

        @media (min-width: 768px) {
            #sidebar.offcanvas {
                width: 300px;
                position: sticky;
                transform: none;
                visibility: visible !important;
                top: var(--my-top-space);
            }
        }

        /* Result tables scroll sideways on a phone instead of breaking the page. */
        @media (max-width: 767.98px) {
            .applicant-profile-content table { display: block; max-width: 100%; overflow-x: auto; }
        }

        #sidebar h6 {
            font-size: .9rem;
        }

        #sidebar li a {
            font-size: var(--zn-fs-ui);
            color: black;
        }

        #sidebar li a.active {
            font-weight: bold;
            color: var(--bs-primary);
        }

        #sidebar li:hover {
            background-color: var(--zn-line-2);
        }

        /* Adjusting scrollbar thickness */
        #sidebar::-webkit-scrollbar {
            width: 7px;  /* Vertical scrollbar width */
            height: 7px; /* Horizontal scrollbar height */
        }

        /* Customize the scrollbar thumb (draggable part) */
        #sidebar::-webkit-scrollbar-thumb {
            background: var(--zn-ink-3);  /* Color of the thumb */
            border-radius: var(--zn-radius-lg);  /* Rounded corners for thumb */
        }
    </style>

    <script>
        $(function() {
            $('#hireModal').on('show.bs.modal', async function(){
                // if($('#form-hire-content').text()) return;
                try {
                    const url = @json(route('applicant.form.hire', ['id' => $applicant?->app_id]));
                    const response = await fetch(url);
                    const html = await response.text();
                    $('#form-hire-content').html(html);
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to load.');
                }
            });

            $('#form-hire-content').on('change', '#hire-dt, #hire-outlet', async function(){
                if(!$('#hire-dt').val() || !$('#hire-outlet').val()) return;
                $('#hire-empno').prop('disabled', true);
                $('#hire-empno').val('generating...');
                try {
                    const url = @json(route('generateEmpNo'));
                    const params = new URLSearchParams({
                        dateHired: $('#hire-dt').val(),
                        area: $('#hire-outlet option:selected').data('area'),
                        outlet: $('#hire-outlet').val()
                    });

                    const response = await fetch(`${url}?${params.toString()}`);
                    const text = await response.text();
                    $('#hire-empno').val(text);
                } catch (error) {
                    $('#hire-empno').val('');
                    console.error('Error:', error);
                    alert('Failed to load.');
                } finally {
                    $('#hire-empno').prop('disabled', false);
                }
            });

            $('#form-hire-content').on('change', '#hire-outlet', async function(){
                $('#hire-area').val($('#hire-outlet option:selected').data('area') || '');
            });

            // $(document).on('submit', '#myForm', function(e) {
            //     e.preventDefault();
            // });
        });

        function toggleHirePw() {
            const input = document.getElementById("hire-pw");
            const btn = document.getElementById("btn-hire-toggle-pw");
            input.type = input.type === "password" ? "text" : "password";
            btn.textContent = input.type === "password" ? "Hide" : "Show";
        }
    </script>

    <div class="row pt-1 justify-content-center">
        <div class="col-md-auto offcanvas offcanvas-start" tabindex="-1" id="sidebar">
            <div class="offcanvas-header d-md-none">
                <h5 class="offcanvas-title">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <ul class="nav flex-column p-3">

                    @can('applicant-documents.view')
                        <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                            <span>Application</span>
                        </h6>
                        <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'documents']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'documents' ? 'active' : '' }}">Documents</a></li>

                        <hr class="my-3">
                    @endcan

                <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Interview Results</span>
                    </h6>

                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'interview-details']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'interview-details' ? 'active' : '' }}">Interview Details</a></li>

                    <hr class="my-3">

                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Profile</span>
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', [ 'id' => $applicant?->app_id, 'tab' => 'personal']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'personal' ? 'active' : '' }}">Personal</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'family']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'family' ? 'active' : '' }}">Family Background</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'skill']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'skill' ? 'active' : '' }}">Special Skills</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'education']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'education' ? 'active' : '' }}">Education</a></li>

                    <hr class="my-3">

                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Professional</span>
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'license']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'license' ? 'active' : '' }}">Eligibility/Licenses</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'certificate']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'certificate' ? 'active' : '' }}">Certificate</a></li>

                    <hr class="my-3">

                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Work</span>
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'employment']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'employment' ? 'active' : '' }}">Employment Record</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'characterref']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'characterref' ? 'active' : '' }}">Character Reference</a></li>

                    <hr class="my-3">

                    {{-- Assessments (HireFlow 2.5): read-only results, each marked with
                         where it stands. The same view for HR and approvers. --}}
                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Assessments</span>
                        @if (isset($assessmentResults))
                            <span class="badge rounded-pill text-bg-light border fw-normal">{{ $assessmentResults->where('hasResult', true)->count() }}/{{ $assessmentResults->count() }}</span>
                        @endif
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'assessment-access']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'assessment-access' ? 'active' : '' }}">Access &amp; Status</a></li>
                    @foreach (collect($assessmentResults ?? [])->groupBy('kind') as $kind => $group)
                        <li class="nav-item px-3 pt-2 small text-body-secondary">{{ $kind === 'aptitude' ? 'Aptitude tests' : 'Questionnaires' }}</li>
                        @foreach ($group as $item)
                            <li class="nav-item">
                                <a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => $item->tab]) }}"
                                   class="nav-link d-flex align-items-center gap-2 {{ ($sub_link ?? '') == $item->tab ? 'active' : '' }}">
                                    <i class="bi {{ match ($item->status) { 'submitted' => 'bi-check-circle-fill text-success', 'timed_out' => 'bi-hourglass-bottom text-secondary', 'interrupted' => 'bi-pause-circle text-warning', 'active' => 'bi-play-circle text-info', default => 'bi-circle text-body-tertiary' } }}"
                                       title="{{ $item->badge[0] ?? '' }}" aria-hidden="true"></i>
                                    <span class="flex-grow-1">{{ $item->label }}</span>
                                    <span class="visually-hidden">— {{ $item->badge[0] ?? '' }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md col-12">
            <button class="btn btn-primary d-md-none my-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">☰ Menu</button>
            <div class="container-fluid">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @include('pages.applicant.partials.profile-overview')

                <div class="d-flex mb-2">
                    <h5>Applicant Profile - {{ $applicant?->first_last_name }}</h5>
                    {{-- <button class="btn btn-outline-secondary btn-sm">Hire</button> --}}
                    @if(($sub_link ?? '') !== 'interview-details')
                    <div class="btn-group dropstart ms-auto">
                        <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Set Status</button>
                        <ul class="dropdown-menu">
                            @can('applicant.hire')
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#hireModal">Hired</button></li>
                            @endcan
                            <li><a class="dropdown-item" href="#">Inactive</a></li>
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="applicant-profile-content">
                    @yield('profile_content')
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @can('applicant.hire')
    <div class="modal fade" id="hireModal" tabindex="-1" aria-labelledby="hireModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="hireModalLabel">Setup Employee Account</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-hire" action="{{ route('applicant.hire', ['id' => $applicant?->app_id]) }}" method="POST">
                    @csrf
                    <div class="modal-body" id="form-hire-content"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    </div>{{-- /.hf-theme --}}
@stop
