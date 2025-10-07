<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Login') }}</title>

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

        <!-- Login box -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative"
            style="background:url({{ asset('freedash/assets/images/big/auth-bg.jpg') }}) no-repeat center center;">
            
            <div class="auth-box row shadow-lg rounded overflow-hidden">
                <!-- Left image -->
                <div class="col-lg-7 col-md-5 modal-bg-img" 
                     style="background-image: url({{ asset('freedash/assets/images/big/3.jpg') }});">
                </div>

                <!-- Right form -->
                <div class="col-lg-5 col-md-7 bg-white">
                    <div class="p-4">
                        <div class="text-center">
                            <img src="{{ asset('freedash/assets/images/big/icon.png') }}" alt="logo" width="70">
                        </div>

                        <h2 class="mt-3 text-center fw-bold">{{ __('Sign In') }}</h2>
                        <p class="text-center text-muted small">
                            {{ __('Enter your email and password to access your account.') }}
                        </p>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success small text-center">{{ session('status') }}</div>
                        @endif

                        <form class="mt-4" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="row">
                                <!-- Email -->
                                <div class="col-12 mb-3">
                                    <label class="form-label text-dark fw-semibold" for="email">{{ __('Email') }}</label>
                                    <input id="email" name="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="{{ __('Enter your email') }}"
                                           value="{{ old('email') }}" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="col-12 mb-3">
                                    <label class="form-label text-dark fw-semibold" for="password">{{ __('Password') }}</label>
                                    <input id="password" name="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="{{ __('Enter your password') }}" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Remember Me -->
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                        <label class="form-check-label small text-muted" for="remember_me">
                                            {{ __('Remember me') }}
                                        </label>
                                    </div>
                                </div>

                                <!-- Forgot password -->
                                @if (Route::has('password.request'))
                                    <div class="col-12 text-end mb-3">
                                        <a class="text-primary small text-decoration-none" href="{{ route('password.request') }}">
                                            {{ __('Forgot your password?') }}
                                        </a>
                                    </div>
                                @endif

                                <!-- Button -->
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn w-100 btn-dark">
                                        {{ __('Sign In') }}
                                    </button>
                                </div>

                                <!-- Register -->
                                @if (Route::has('register'))
                                    <div class="col-12 text-center mt-4">
                                        <p class="text-muted mb-0 small">
                                            {{ __("Don't have an account?") }}
                                            <a href="{{ route('register') }}" class="text-danger fw-semibold">
                                                {{ __('Sign Up') }}
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('freedash/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script>
        $(".preloader").fadeOut();
    </script>
</body>
</html>
