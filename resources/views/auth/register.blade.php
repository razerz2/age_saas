<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Register') }}</title>

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

        <!-- Register box -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative"
             style="background:url({{ asset('freedash/assets/images/big/auth-bg.jpg') }}) no-repeat center center;">
             
            <div class="auth-box row text-center shadow-lg rounded overflow-hidden">
                <!-- Left image -->
                <div class="col-lg-7 col-md-5 modal-bg-img"
                     style="background-image: url({{ asset('freedash/assets/images/big/3.jpg') }});">
                </div>

                <!-- Right form -->
                <div class="col-lg-5 col-md-7 bg-white">
                    <div class="p-4">
                        <img src="{{ asset('freedash/assets/images/big/icon.png') }}" alt="logo" width="70">
                        <h2 class="mt-3 text-center fw-bold">{{ __('Sign Up for Free') }}</h2>
                        <p class="text-muted small mb-4">{{ __('Create your account to access the system.') }}</p>

                        <form class="mt-4" method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row">

                                <!-- Name -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('name') is-invalid @enderror"
                                           type="text" name="name"
                                           placeholder="{{ __('Your Name') }}"
                                           value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('email') is-invalid @enderror"
                                           type="email" name="email"
                                           placeholder="{{ __('Email Address') }}"
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('password') is-invalid @enderror"
                                           type="password" name="password"
                                           placeholder="{{ __('Password') }}" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('password_confirmation') is-invalid @enderror"
                                           type="password" name="password_confirmation"
                                           placeholder="{{ __('Confirm Password') }}" required>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Button -->
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn w-100 btn-dark">
                                        {{ __('Sign Up') }}
                                    </button>
                                </div>

                                <!-- Already have account -->
                                <div class="col-12 text-center mt-4">
                                    <p class="text-muted small mb-0">
                                        {{ __('Already have an account?') }}
                                        <a href="{{ route('login') }}" class="text-danger fw-semibold">
                                            {{ __('Sign In') }}
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
