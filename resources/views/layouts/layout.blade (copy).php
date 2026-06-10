<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- <script src="{{ asset('jquery-3.7.1.min.js') }}"></script> -->

    <!-- <link rel="stylesheet" href="{{ asset('bootstrap-5.3.3-dist/css/bootstrap.min.css') }}"> -->

    <!-- <script src="{{ asset('popper.min.js') }}"></script> -->
    <!-- <script src="{{ asset('bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js') }}"></script> -->

    <link rel="stylesheet" href="{{ asset('main.css') }}">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-0" aria-label="Navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin</a>
            <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Sign Out</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar border-end p-0 bg-dark" style="display: none;">
        <div class="offcanvas-md offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="sidebarMenuLabel">Company name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">

                <ul class="nav flex-column pe-1">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 rounded active" aria-current="page" href="/portal-admin">
                            <svg class="bi">
                                <use xlink:href="#house-fill"></use>
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 rounded" aria-current="page" href="/portal-admin/employee">
                            <svg class="bi">
                                <use xlink:href="#house-fill"></use>
                            </svg>
                            Employee Information
                        </a>
                    </li>

                    <!-- <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Employee Information</span>
                    </h6>
                    <li class="nav-item-dropdown">
                        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#profile-collapse" aria-expanded="false">
                            Profile
                        </button>
                        <div class="collapse" id="profile-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Personal</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Family Background</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Special Skills</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item-dropdown">
                        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#education-collapse" aria-expanded="false">
                            Education
                        </button>
                        <div class="collapse" id="education-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Education</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Eligibility/Licenses</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Certificate</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item-dropdown">
                        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#work-collapse" aria-expanded="false">
                            Work
                        </button>
                        <div class="collapse" id="work-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Employment Record</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Internal Certificate</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Payslip</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Contracts</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Character Reference</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item-dropdown">
                        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#personality-test-collapse" aria-expanded="false">
                            Personality Test
                        </button>
                        <div class="collapse" id="personality-test-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Enneagram</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">TAPT</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">DISC</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">Multiple Intelligent Quotient</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">What color are you?</a></li>
                                <li class="nav-item"><a href="#" class="nav-link d-flex align-items-center gap-2 rounded">VAK</a></li>
                            </ul>
                        </div>
                    </li> -->
                </ul>

                <!-- <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
                    <span>Saved reports</span>
                    <a class="link-secondary" href="#" aria-label="Add a new report">
                        <svg class="bi">
                            <use xlink:href="#plus-circle"></use>
                        </svg>
                    </a>
                </h6>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#">
                            <svg class="bi">
                                            <use xlink:href="#file-earmark-text"></use>
                                        </svg>
                            Current month
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#">
                            <svg class="bi">
                                <use xlink:href="#file-earmark-text"></use>
                            </svg>
                            Last quarter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#">
                            <svg class="bi">
                                <use xlink:href="#file-earmark-text"></use>
                            </svg>
                            Social engagement
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#">
                            <svg class="bi">
                                <use xlink:href="#file-earmark-text"></use>
                            </svg>
                            Year-end sale
                        </a>
                    </li>
                </ul> -->

                <hr class="my-3">

                <ul class="nav flex-column mb-auto pe-1">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 rounded" href="#">
                            <svg class="bi">
                                <use xlink:href="#gear-wide-connected"></use>
                            </svg>
                            Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 rounded" href="#">
                            <svg class="bi">
                                <use xlink:href="#door-closed"></use>
                            </svg>
                            Sign out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- <main class="ms-sm-auto ms-custom px-md-4"> -->
    <main class="container-fluid">
        @yield('content')
    </main>
</body>

</html>