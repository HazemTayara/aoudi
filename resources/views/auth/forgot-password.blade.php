@extends('layouts.guest')
@section('content')
    <div class="card">
        <div class="card-header text-center">
            <h3>نسيت كلمة المرور</h3>
        </div>
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">إرسال البريد الألكتروني</button>
            </form>
            <hr>
        </div>
@endsection