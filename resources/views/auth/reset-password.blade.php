@extends('layouts.guest')
@section('content')
    <div class="card">
        <div class="card-header text-center">
            <h3>إعادة تعيين كلمة المرور</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="reset_token" value="{{ $resetToken }}">

                <div class="mb-3">
                    <label>كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label>تأكيد كلمة المرور الجديدة</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">إعادة تعيين كلمة المرور</button>
            </form>
        </div>
    </div>
@endsection