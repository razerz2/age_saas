<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Reset Password') }}</title>

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

        <!-- Reset Password -->
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
                        <h2 class="mt-3 text-center fw-bold">{{ __('Reset Password') }}</h2>
                        <p class="text-muted small mb-4">
                            {{ __('Enter your new password to regain access to your account.') }}
                        </p>

                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf
                            <!-- Token -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="row">
                                <!-- Email -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('email') is-invalid @enderror"
                                           type="email"
                                           name="email"
                                           value="{{ old('email', $request->email) }}"
                                           placeholder="{{ __('Email Address') }}"
                                           required autofocus autocomplete="username">
                                    @error('email')
                                        <div class="invalid-feedback text-start">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('password') is-invalid @enderror"
                                           type="password"
                                           name="password"
                                           placeholder="{{ __('New Password') }}"
                                           required autocomplete="new-password">
                                    @error('password')
                                        <div class="invalid-feedback text-start">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-12 mb-3">
                                    <input class="form-control @error('password_confirmation') is-invalid @enderror"
                                           type="password"
                                           name="password_confirmation"
                                           placeholder="{{ __('Confirm Password') }}"
                                           required autocomplete="new-password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback text-start">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Button -->
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn w-100 btn-dark">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>

                                <!-- Back to login -->
                                <div class="col-12 text-center mt-4">
                                    <p class="text-muted small mb-0">
                                        {{ __('Remember your password?') }}
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
