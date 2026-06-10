<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style type="text/css">
        /* #logo {
            max-width: 100%;
        } */

        .form-floating,
        .form-floating *,
        .form-floating::after,
        .form-floating *::after {
            background-color: transparent !important;
        }

        body {
            background-color: #f4f4f4;
        }

        form > button,
        input.form-control {
            font-size: 20px !important;
        }

        form > .input-group {
            background-color: #f0f0f0;
        }

        input.form-control:focus {
            outline: none;
            box-shadow: none;
        }

        .gap-custom {
            gap: 70px;
        }

        @media (min-width: 768px) {
            .mt-md-custom {
                margin-top: 3.5rem !important;
            }
        }
    </style>
</head>

<body class="vh-100">
    <div class="container-fluid">
        <div class="row mt-3 gap-custom align-items-center justify-content-center">
            <div class="col-md-auto text-center" style="color: #64402f;">
                <img id="logo" src="https://teamtngc.com/zen/assets/img/coffi.png"
                    class="img-fluid card-img-top mx-auto" alt="zen-icon">
                <h1>Zenhub <small class="badge align-middle" style="background-color: #64402f; color: white; font-size: 12px;">Admin</small></h1>
                <p class="card-text">Your Daily Dose of Energy & Inspiration.</p>
            </div>

            <div class="col-md-4 mt-md-custom">
                <form class="mb-3" method="POST" action="{{ route('login') }}">
                    @if ($errors->any())
                        <div class="alert alert-danger p-1" role="alert">
                            @foreach ($errors->all() as $error)
                                <span class="fw-medium d-block">{{ $error }}</span>
                            @endforeach
                        </div>
                    @endif
                    @csrf
                    
                    <div class="input-group mb-3 rounded-pill">
                        <span class="border-0 input-group-text p-3 fs-5 text-body-tertiary rounded-start-pill bg-transparent"><i class="fa fa-user"></i></span>
                        <input type="text" class="border-0 form-control rounded-end-pill bg-transparent" name="username" id="username" value="{{ old('username') }}" placeholder="Username" aria-label="Username" aria-describedby="" required>
                    </div>

                    <div class="input-group mb-3 rounded-pill">
                        <span class="border-0 input-group-text p-3 fs-5 text-body-tertiary rounded-start-pill bg-transparent"><i class="fa fa-lock"></i></span>
                        <input type="password" class="border-0 form-control rounded-end-pill bg-transparent" name="password" id="password" placeholder="Password" aria-label="Password" aria-describedby="" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
