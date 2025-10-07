<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Confirm Password') }}</title>

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

        <!-- Confirm Password -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative"
             style="background:url({{ asset('freedash/assets/images/big/auth-bg.jpg') }}) no-repeat center center;">

            <div class="auth-box row text-center shadow-lg rounded overflow-hidden">
                <!-- Left Image -->
                <div class="col-lg-7 col-md-5 modal-bg-img"
                     style="background-image: url({{ asset('freedash/assets/images/big/3.jpg') }});">
                </div>

                <!-- Right Form -->
                <div class="col-lg-5 col-md-7 bg-white">
                    <div class="p-4">
                        <img src="{{ asset('freedash/assets/images/big/icon.png') }}" alt="logo" width="70">
                        <h2 class="mt-3 text-center fw-bold">{{ __('Confirm Password') }}</h2>
                        <p class="text-muted small mb-4">
                            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                        </p>

                        <form method="POST" action="{{ route('password.confirm') }}">
                            @csrf
                            <div class="row">
                                <!-- Password -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           type="password"
                                           name="password"
                                           placeholder="{{ __('Password') }}"
                                           required autocomplete="current-password">
                                    @error('password')
                                        <div class="invalid-feedback text-start">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Submit -->
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn w-100 btn-dark">
                                        {{ __('Confirm Password') }}
                                    </button>
                                </div>

                                <!-- Optional back link -->
                                <div class="col-12 text-center mt-4">
                                    <p class="text-muted small mb-0">
                                        <a href="{{ route('login') }}" class="text-danger fw-semibold">
                                            {{ __('Back to Login') }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </form>

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
