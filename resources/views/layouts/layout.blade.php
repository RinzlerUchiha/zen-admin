<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="url-prefix" content="{{ config('app.url') }}">
    <meta name="base-url" content="{{ url('/') }}">
    <link rel="icon" href="https://teamtngc.com/zen/assets/img/coffi.png" type="image/png">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- <script src="{{ asset('jquery-3.7.1.min.js') }}"></script> -->
    <!-- <link rel="stylesheet" href="{{ asset('bootstrap-5.3.3-dist/css/bootstrap.min.css') }}"> -->
    <!-- <script src="{{ asset('popper.min.js') }}"></script> -->
    <!-- <script src="{{ asset('bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js') }}"></script> -->


    {{-- <link rel="stylesheet" href="{{ asset('_content.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('main.css') }}">
    <style>
        #site-logo,
        #usr-img {
            /* transform: scale(1.5); */
            height: 30px;
        }

        body > nav.navbar .nav-item .nav-link{
            padding-top: .25rem;
            padding-bottom: .25rem;
            font-size: 20px;
        }

        body > nav.navbar .nav-item .nav-link:hover{
            transform: scale(1.3);
        }
    </style>

    <script>
        $.fn.autoResize = function() {
            this.each(function() {
                var textarea = $(this);

                // Set the max height to 50% of the viewport height
                var maxHeight = $(window).height() * 0.5;

                // Function to resize the textarea
                function resize() {
                    // Reset height to auto to allow shrinking
                    // textarea.height('auto');

                    // Set the height based on the scrollHeight (content height)
                    var newHeight = textarea[0].scrollHeight;

                    if(textarea[0].clientHeight < newHeight){
                        // Apply the height, but don't exceed the maxHeight
                        textarea.height(Math.min(newHeight, maxHeight));
                    }
                }

                // Unbind any previous .autoResize namespaced input event before rebinding
                textarea.off('input.autoResize').on('input.autoResize', resize);
                // Bind the input event to trigger resize on every input
                textarea.on('input', resize);

                // Initial call to resize to set the correct height on page load
                resize();
            });

            return this;  // Return the jQuery object to maintain chainability
        };
    </script>

    <script>
        // Store the original fetch function
        const originalFetch = window.fetch;

        // Retrieve the URL prefix from the meta tag
        const urlPrefix = document.querySelector('meta[name="url-prefix"]')?.getAttribute('content') || '';

        // Override the global fetch function
        window.fetch = function(input, init = {}) {
            // If the URL prefix is not empty, and the request URL is relative, prepend the prefix
            if (urlPrefix && !input.startsWith('http') && !input.startsWith('https') && !input.startsWith(urlPrefix)) {
                // Convert input to a string if it's a URL object
                if (typeof input === 'string') {
                    input = urlPrefix + input;
                } else {
                    input.url = urlPrefix + input.url;
                }
            }

            // Call the original fetch function with the modified URL
            return originalFetch(input, init);
        };

    </script>

    <script>
        const BASE = document.querySelector('meta[name="base-url"]').content;
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        const DEFAULT_HEADERS = {
            'Accept': 'application/json'
        };

        async function api(path, options = {}) {
            const res = await fetch(`${BASE}/${path}`, {
                headers: {
                    ...DEFAULT_HEADERS,
                    'X-CSRF-TOKEN': CSRF,
                    ...(options.headers || {})
                },
                ...options
            });

            if (!res.ok) {
                const error = await res.text();
                throw new Error(error || 'Request failed');
            }

            return res.json();
        }

        const request = (method, url, data = null, options = {}) => {
            const hasData = data !== null && data !== undefined;
            const isFormData = data instanceof FormData;

            const config = {
                ...options,
                method,
                headers: {
                    'Accept': 'application/json',
                    ...(!isFormData && hasData ? { 'Content-Type': 'application/json' } : {}),
                    ...(options.headers || {})
                }
            };

            if (hasData) {
                config.body = isFormData ? data : JSON.stringify(data);
            }

            return api(url, config);
        };

        const GET = (url, options = {}) => request('GET', url, null, options);
        const POST = (url, data, options = {}) => request('POST', url, data, options);
        const PUT = (url, data, options = {}) => request('PUT', url, data, options);
        const DELETE_REQ = (url, options = {}) => request('DELETE', url, null, options);

        // await request('GET', 'api/users');

        // await request('POST', 'api/users', {
        //     name: 'John'
        // });

        // await request('PUT', 'api/users/1', {
        //     name: 'Updated'
        // });

        // await request('GET', 'api/users', null, {
        //     credentials: 'include',
        //     headers: {
        //         'X-CUSTOM-HEADER': 'abc'
        //     }
        // });
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
@stack('styles')
</head>

<body>
    {{-- <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" aria-label="Navbar"> --}}
    <nav class="navbar navbar-expand bg-body-tertiary fixed-top" aria-label="Navbar">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand d-flex align-items-center" href="{{ config('app.url') }}/">
                <img class="img-fluid" id="site-logo" src="https://teamtngc.com/zen/assets/img/coffi.png" alt="Logo" class="d-inline-block align-text-top">
                <span class="fs-5 ms-1" style="color: #5d2502;">Zenhub</span>
            </a>
            <a class="navbar-toggler" role="button" data-bs-toggle="offcanvas" href="#servicesMenu" aria-controls="servicesMenu">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </a>
            <div class="offcanvas offcanvas-start bg-body-tertiary" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="sidebarMenuLabel">Zen-Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body overflow-y-auto-md">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <!-- <li class="nav-item">
                            <a class="nav-link {{ $main_link == 'dashboard' ? 'active' : '' }}" aria-current="{{ $main_link == 'dashboard' ? 'page' : '' }}" href="/">Dashboard</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link {{ $main_link == 'employee' ? 'active' : '' }}" aria-current="{{ $main_link == 'employee' ? 'page' : '' }}" href="/employee">Employee</a>
                        </li> --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $main_link == 'employee' ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Employee</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ $sub_link == 'employee' ? 'active' : '' }}" aria-current="{{ $sub_link == 'employee' ? 'page' : '' }}" href="/employee">Employee Information</a>
                                </li>
                                <li><a class="dropdown-item {{ $sub_link == 'new' ? 'active' : '' }}" aria-current="{{ $sub_link == 'new' ? 'page' : '' }}" href="/employee/new">Add New Employee</a></li>
                                {{-- <li><hr class="dropdown-divider"></li> --}}
                                {{-- <li><a class="dropdown-item" href="#">Something else here</a></li> --}}
                            </ul>
                        </li> -->
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" role="button" data-bs-toggle="offcanvas" href="#servicesMenu" aria-controls="servicesMenu">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown" id="user-dropdown">
                            <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <img id="usr-img" src="{{ config('app.url')."/profile/img/".Auth::user()->Emp_No }}" class="rounded-circle img-fluid" alt="user image">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h5 class="dropdown-header">{{ Auth::user()->FirstLastName }}</h5></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end bg-body-tertiary" data-bs-backdrop="false" tabindex="-1" id="servicesMenu" aria-labelledby="servicesMenuLabel">
        <!-- <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="servicesMenuLabel">Services</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#servicesMenu" aria-label="Close"></button>
        </div> -->
        <div class="offcanvas-body overflow-y-auto-md">
            <button type="button" class="btn-close float-end" data-bs-dismiss="offcanvas" data-bs-target="#servicesMenu" aria-label="Close"></button>
            <ul class="nav"><li class="nav-item h6">HIRING</li></ul>
            <ul class="nav row row-cols-4">
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ route('applicant.index') }}">
                        {{-- <i class="bi bi-people"></i> --}}
                        {{-- <svg width="30" height="30" class="rounded-circle">
                            <image width="30" height="30" href="{{ asset('icon/applicant.png') }}"  x="0" y="0" alt="...">
                        </svg> --}}
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/applicant.png') }}">
                        <span class="mt-1">Applicant</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/manpower">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/prf.png') }}">
                        <span class="mt-1">PRF</span>
                    </a>
                </li>
            </ul>

            <hr class="mt-1">

            <ul class="nav"><li class="nav-item h6">RECORDS</li></ul>
            <ul class="nav row row-cols-4">
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/employee">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/201.png') }}">
                        <span class="mt-1">201</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/report">
                        <i class="bi bi-file-text" style="width: 30px; height: 30px;"></i>
                        <span class="mt-1">Report</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" id="link-grievance" href="{{ config('app.url') }}/grievance">
                        <i class="bi bi-file-text" style="width: 30px; height: 30px;"></i>
                        <span class="mt-1">Grievance</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/contracts">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/contracts.png') }}">
                        <span class="mt-1">Contracts</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/clearance">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/clearance.png') }}">
                        <span class="mt-1">Clearance</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/kamustahan">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/kamustahan.png') }}">
                        <span class="mt-1">Kamustahan</span>
                    </a>
                </li>
            </ul>

            <hr class="mt-1">

            <ul class="nav"><li class="nav-item h6">Training</li></ul>
            <ul class="nav row row-cols-4">
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="#">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/academy-report.png') }}">
                        <span class="mt-1">Academy Reports</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="#">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/training-bond.png') }}">
                        <span class="mt-1">Training Bond</span>
                    </a>
                </li>
            </ul>

            <hr class="mt-1">

            <ul class="nav"><li class="nav-item h6">Others</li></ul>
            <ul class="nav row row-cols-4">
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/announcement">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/announcement.png') }}">
                        <span class="mt-1">Announcements</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/events">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/events.png') }}">
                        <span class="mt-1">Events</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/memo">
                        <i class="bi bi-file-text" style="width: 30px; height: 30px;"></i>
                        <span class="mt-1">Memo</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="#">
                        <img class="rounded-circle" width="30" height="30" src="{{ asset('icon/phoneagree.png') }}">
                        <span class="mt-1">Phone Agreement</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/admin">
                        <i class="bi bi-file-text" style="width: 30px; height: 30px;"></i>
                        <span class="mt-1">Admin</span>
                    </a>
                </li>
                <li class="nav-item col">
                    <a class="nav-link d-flex flex-column align-items-center text-center" href="{{ config('app.url') }}/maintenance">
                        <i class="bi bi-gear" style="width: 30px; height: 30px;"></i>
                        <span class="mt-1">Maintenance</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- <main class="ms-sm-auto ms-custom px-md-4"> -->
    <main class="container-fluid">
        @yield('content')
    </main>

    <script type="text/javascript">
    
        $(function(){
            fetchGrievanceNotifications();
        });

        async function fetchGrievanceNotifications() {
            try {
                let total_grievance_notification = 0;
                const response1 = await fetch('/grievance/ir/notifications');
                const data1 = await response1.json();
                let total_ir = Object.values(data1).reduce((total, num) => total + num, 0);

                const response2 = await fetch('/grievance/13a/notifications');
                const data2 = await response2.json();
                let total_13a = Object.values(data2).reduce((total, num) => total + num, 0);

                const response3 = await fetch('/grievance/13b/notifications');
                const data3 = await response3.json();
                let total_13b = Object.values(data3).reduce((total, num) => total + num, 0);

                total_grievance_notification = total_ir + total_13a + total_13b;

                $('a[href="#servicesMenu"]').addClass('has-notification');
                $('#link-grievance > span').attr('notification-cnt', total_grievance_notification);
            } catch (error) {
                console.error('Fetch error:', error);
            }
        }
    </script>
</body>

</html>