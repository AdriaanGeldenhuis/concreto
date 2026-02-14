@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="container" style="max-width: 400px; padding-top: 3rem;">
    <div class="card">
        <div class="card-header">Login to your account</div>
        <div class="card-body">
            <form method="POST" action="{{ route('login') }}{{ request('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-2">Login</button>
            </form>
            <p class="text-center mt-2 text-small">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
        </div>
    </div>
</div>
@endsection
