@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card my-auto shadow login">
            <div><span class="cardheader">{{ __('Register') }}</span><br/><span class="subtitle">Create an account by filling the form below.</span></div>

                <div class="card-body login">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control logintext @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="first_name" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('First Name') }}</label>

                            <div class="col-md-6">
                                <input id="first_name" type="text" class=" logintext form-control" name="first_name" >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="last_name" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('Last Name') }}</label>
                         
                            <div class="col-md-6">
                                <input id="last_name" type="text" class="logintext form-control" name="last_name" >
                            </div>
                        </div>

                        <div class="row mb-3">
                        <label for="email" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control logintext @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="profile_picture" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('Profile Picture') }}</label>

                            <div class="col-md-6">
                                <input id="profile_picture" type="file" class="logintext form-control @error('profile_picture') is-invalid @enderror" name="profile_picture">

                                @error('profile_picture')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control logintext @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end labelRegister">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control logintext" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary loginbutton">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
