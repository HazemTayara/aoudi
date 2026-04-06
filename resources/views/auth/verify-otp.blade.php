@extends('layouts.guest')
@section('content')
    <div class="card">
        <div class="card-header text-center">
            <h3>تأكيد رمز التحقق</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.verify') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label>رمز التحقق (OTP)</label>
                    <input type="text" name="otp" class="form-control text-center" placeholder="_ _ _ _ _ _" maxlength="6"
                        required autofocus>
                    @error('otp') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">تحقق</button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('password.request') }}">إعادة إرسال الرمز</a>
            </div>
        </div>
    </div>
@endsection