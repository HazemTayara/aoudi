@extends('layouts.guest')

@section('title', 'تأكيد رمز التحقق')

@section('content')
    <div class="glass-card">
        <!-- Logo Section -->
        <div class="logo-container">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" alt="شحن العودة">
            </div>
        </div>

        <div class="card-header-custom">
            <h3>تأكيد رمز التحقق</h3>
            <p>أدخل الرقم المكون من 6 أرقام الذي تم إرساله إلى بريدك الإلكتروني</p>
        </div>

        <div class="card-body-custom">
            @if ($errors->any())
                <div class="alert alert-danger"
                    style="border-radius: 12px; border: none; background: #f8d7da; color: #721c24; padding: 12px 20px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.verify') }}" id="otpForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token ?? '' }}">

                <div class="form-floating-custom">
                    <input type="text" name="otp" id="otp" class="form-control text-center" placeholder="_ _ _ _ _ _"
                        maxlength="6" required autofocus
                        style="text-align: center; font-size: 24px; letter-spacing: 10px; font-weight: bold;">
                    <i class="fas fa-qrcode form-icon"></i>
                    {{-- <label for="otp">رمز التحقق (OTP)</label> --}}
                    @error('otp')
                        <div class="text-danger mt-1 small">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-modern w-100 text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    تحقق من الرمز
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('password.request') }}" class="forgot-link" id="resendLink">
                        <i class="fas fa-redo-alt me-1"></i>
                        إعادة إرسال الرمز
                    </a>
                    <div id="countdown" class="mt-2 small text-muted" style="display: none;"></div>
                </div>

                <div class="text-center mt-3">
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
        // Auto-format OTP input and auto-submit when 6 digits entered
        const otpInput = document.getElementById('otp');
        const otpForm = document.getElementById('otpForm');

        otpInput?.addEventListener('input', function (e) {
            // Remove any non-digits
            this.value = this.value.replace(/[^0-9]/g, '');

            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                setTimeout(() => {
                    otpForm.submit();
                }, 500);
            }
        });

        // Resend functionality with countdown timer
        const resendLink = document.getElementById('resendLink');
        const countdownDiv = document.getElementById('countdown');
        let timer = null;

        function startCountdown(seconds = 60) {
            let remaining = seconds;
            resendLink.style.pointerEvents = 'none';
            resendLink.style.opacity = '0.5';
            countdownDiv.style.display = 'block';

            timer = setInterval(() => {
                if (remaining <= 0) {
                    clearInterval(timer);
                    resendLink.style.pointerEvents = 'auto';
                    resendLink.style.opacity = '1';
                    countdownDiv.style.display = 'none';
                } else {
                    countdownDiv.innerHTML = `يمكنك إعادة الإرسال بعد ${remaining} ثانية`;
                    remaining--;
                }
            }, 1000);
        }

        resendLink?.addEventListener('click', function (e) {
            e.preventDefault();

            // Show loading state
            Swal.fire({
                title: 'جاري الإرسال...',
                text: 'يرجى الانتظار',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request to resend OTP
            $.ajax({
                url: '{{ route("password.request") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    email: '{{ old("email", session("email")) ?? "" }}'
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الإرسال',
                        text: 'تم إرسال رمز التحقق الجديد إلى بريدك الإلكتروني',
                        confirmButtonColor: '#F6BE00',
                        timer: 3000
                    });
                    startCountdown(60);
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: xhr.responseJSON?.message || 'حدث خطأ، يرجى المحاولة مرة أخرى',
                        confirmButtonColor: '#F6BE00'
                    });
                }
            });
        });

        document.getElementById('otpForm')?.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري التحقق...';
            submitBtn.disabled = true;
        });

        // Start countdown if page loaded with email (after first request)
        @if(session('status') || old('email'))
            startCountdown(60);
        @endif
    </script>
@endpush