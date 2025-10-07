<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Verify Email') }}</title>

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('freedash/assets/images/favicon.png') }}">
    <link href="{{ asset('freedash/dist/css/style.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="main-wrapper">
        <!-- Preloader -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>

        <!-- Verify Email -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative"
             style="background:url({{ asset('freedash/assets/images/big/auth-bg.jpg') }}) no-repeat center center;">

            <div class="auth-box row text-center shadow-lg rounded overflow-hidden">
                <!-- Left Image -->
                <div class="col-lg-7 col-md-5 modal-bg-img"
                     style="background-image: url({{ asset('freedash/assets/images/big/3.jpg') }});">
                </div>

                <!-- Right Content -->
                <div class="col-lg-5 col-md-7 bg-white">
                    <div class="p-4">
                        <img src="{{ asset('freedash/assets/images/big/icon.png') }}" alt="logo" width="70">
                        <h2 class="mt-3 text-center fw-bold">{{ __('Verify Your Email Address') }}</h2>

                        <p class="text-muted small mb-4 text-start">
                            {{ __('Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just sent you. If you didn’t receive the email, we’ll gladly send you another.') }}
                        </p>

                        <!-- Status message -->
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success text-start small py-2">
                                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="mt-4">
                            <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                                @csrf
                                <button type="submit" class="btn w-100 btn-dark">
                                    {{ __('Resend Verification Email') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn w-100 btn-outline-secondary">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('freedash/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script>
        $(".preloader").fadeOut();
    </script>
</body>
</html>
