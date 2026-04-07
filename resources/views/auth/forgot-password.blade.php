@extends('layouts.guest')

@section('title', 'نسيت كلمة المرور')

@section('content')
    <div class="glass-card">
        <!-- Logo Section -->
        <div class="logo-container">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" alt="شحن العودة">
            </div>
        </div>

        <div class="card-header-custom">
            <h3>نسيت كلمة المرور؟</h3>
            <p>لا تقلق! أدخل بريدك الإلكتروني وسنرسل لك رمز التحقق</p>
        </div>

        <div class="card-body-custom">
            @if (session('status'))
                <div class="alert-modern">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger"
                    style="border-radius: 12px; border: none; background: #f8d7da; color: #721c24; padding: 12px 20px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                @csrf

                <div class="form-floating-custom">
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required
                        autofocus placeholder="البريد الإلكتروني">
                    <i class="fas fa-envelope form-icon"></i>
                    {{-- <label for="email">البريد الإلكتروني</label> --}}
                    @error('email')
                        <div class="text-danger mt-1 small">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-modern w-100 text-white">
                    <i class="fas fa-paper-plane me-2"></i>
                    إرسال رمز التحقق
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="forgot-link">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة إلى تسجيل الدخول
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('forgotForm')?.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري الإرسال...';
            submitBtn.disabled = true;
        });
    </script>
@endpush