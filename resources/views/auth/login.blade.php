@extends('layouts.app')

@section('extra_wrapper_classes', 'd-flex flex-column justify-content-center')
@section('title', trans('auth.login.title'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            @if ($showLoginNotice)
                <div class="card-header bg-info text-white"><strong>@lang('auth.login.header_notice')</strong></div>
            @endif

            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group row">
                        <label for="email" class="col-sm-4 col-form-label text-md-right">{{ trans('auth.login.email') }}</label>

                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="password" class="col-md-4 col-form-label text-md-right">@lang('auth.login.password')</label>

                        <div class="col-md-6">
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- <div class="form-group row">
                        <div class="col-md-6 offset-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                </label>
                            </div>
                        </div>
                    </div> --}}

                    <div class="form-group row">
                        <div class="col-md-8 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                @lang('auth.login.login')
                            </button>

                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                @lang('auth.login.forget_pass')
                            </a>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-8 offset-md-4">
                            @lang('auth.login.first_visit')

                            <a class="btn btn-secondary ml-2" href="{{ route('register') }}">
                                @lang('auth.login.register')
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
