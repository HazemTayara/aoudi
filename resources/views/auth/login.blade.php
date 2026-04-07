{{-- login.blade.php --}}
@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="glass-card">
        <!-- Logo Section -->
        <div class="logo-container">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" alt="شحن العودة">
            </div>
        </div>

        <div class="card-header-custom">
            <h3>مرحباً بك</h3>
            <p>سجل الدخول للوصول إلى لوحة التحكم</p>
        </div>

        <div class="card-body-custom">
            @if(session('status'))
                <div class="alert-modern">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
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

                <div class="form-floating-custom">
                    <input type="password" name="password" id="password" class="form-control" required placeholder="كلمة المرور">
                    <i class="fas fa-lock form-icon"></i>
                        {{-- <label for="password">كلمة المرور</label> --}}
                    @error('password')
                        <div class="text-danger mt-1 small">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-modern w-100 text-white">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    تسجيل الدخول
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('password.request') }}" class="forgot-link">
                        <i class="fas fa-key me-1"></i>
                        نسيت كلمة المرور؟
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Add loading state on form submit
        document.getElementById('loginForm')?.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري التسجيل...';
            submitBtn.disabled = true;

            // Optional: You can remove this timeout if you want to keep loading until response
            // This is just for demonstration
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 3000);
        });
    </script>
@endpush