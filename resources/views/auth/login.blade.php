@extends('layouts.guest')
@section('content')
    <div class="card">
        <div class="card-header text-center">
            <h3>تسجيل الدخول</h3>
        </div>
        <div class="card-body">
            @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div> @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">دخول</button>
                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                </div>
            </form>
        </div>
    </div>
@endsection