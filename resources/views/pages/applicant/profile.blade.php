@extends('layouts.layout')

@section('content')

    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script> --}}

    <style>
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

        #sidebar h6 {
            font-size: .9rem;
        }

        #sidebar li a {
            font-size: 12px;
            color: black;
        }

        #sidebar li a.active {
            font-weight: bold;
            color: var(--bs-primary);
        }

        #sidebar li:hover {
            background-color: #d1d1d1;
        }

        /* Adjusting scrollbar thickness */
        #sidebar::-webkit-scrollbar {
            width: 7px;  /* Vertical scrollbar width */
            height: 7px; /* Horizontal scrollbar height */
        }

        /* Customize the scrollbar thumb (draggable part) */
        #sidebar::-webkit-scrollbar-thumb {
            background: #8b8a8a;  /* Color of the thumb */
            border-radius: 10px;  /* Rounded corners for thumb */
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

                    <h6 class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Personality Test</span>
                    </h6>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'enneagram']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'enneagram' ? 'active' : '' }}">Enneagram</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'tapt']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'tapt' ? 'active' : '' }}">TAPT</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'disc']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'disc' ? 'active' : '' }}">DISC</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'miq']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'miq' ? 'active' : '' }}">Multiple Intelligent Quotient</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'color']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'color' ? 'active' : '' }}">What color are you?</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'vak']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'vak' ? 'active' : '' }}">VAK</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'why-i-work']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'why-i-work' ? 'active' : '' }}">Why I Work</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'career-anchors']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'career-anchors' ? 'active' : '' }}">Career Anchors</a></li>
                    <hr class="my-1 mx-3">
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'abstract-reasoning']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'abstract-reasoning' ? 'active' : '' }}">Basic Abstract Reasoning</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'basic-math']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'basic-math' ? 'active' : '' }}">Basic Math</a></li>
                    <li class="nav-item"><a href="{{ route('applicant.show', ['id' => $applicant?->app_id, 'tab' => 'maya']) }}" class="nav-link align-items-center gap-2 {{ ($sub_link ?? '') == 'maya' ? 'active' : '' }}">Maya</a></li>
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
                <div class="d-flex mb-2">
                    <h5>Applicant Profile - {{ $applicant?->first_last_name }}</h5>
                    {{-- <button class="btn btn-outline-secondary btn-sm">Hire</button> --}}
                    <div class="btn-group dropstart ms-auto">
                        <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Set Status</button>
                        <ul class="dropdown-menu">
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#hireModal">Hired</button></li>
                            <li><a class="dropdown-item" href="#">Inactive</a></li>
                            {{-- <li><a class="dropdown-item" href="#">Action three</a></li> --}}
                        </ul>
                    </div>
                </div>
                @yield('profile_content')
            </div>
        </div>
    </div>

    <!-- Modal -->
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

@stop
