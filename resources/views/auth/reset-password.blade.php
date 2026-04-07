@extends('layouts.guest')

@section('title', 'إعادة تعيين كلمة المرور')

@section('content')
    <div class="glass-card">
        <!-- Logo Section -->
        <div class="logo-container">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" alt="شحن العودة">
            </div>
        </div>
        
        <div class="card-header-custom">
            <h3>إعادة تعيين كلمة المرور</h3>
            <p>أدخل كلمة مرور جديدة قوية لحسابك</p>
        </div>
        
        <div class="card-body-custom">
            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius: 12px; border: none; background: #f8d7da; color: #721c24; padding: 12px 20px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                @csrf
                <input type="hidden" name="reset_token" value="{{ $resetToken }}">
                
                <div class="form-floating-custom">
                    <input type="password" name="password" id="password" class="form-control" required placeholder="كلمة المرور الجديدة">
                    <i class="fas fa-lock form-icon"></i>
                    {{-- <label for="password">كلمة المرور الجديدة</label> --}}
                    <div class="password-strength mt-2" style="display: none;">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <small class="text-muted" id="strengthText"></small>
                    </div>
                    @error('password')
                        <div class="text-danger mt-1 small">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                
                <div class="form-floating-custom">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="تأكيد كلمة المرور الجديدة">
                    <i class="fas fa-check-circle form-icon"></i>
                    {{-- <label for="password_confirmation">تأكيد كلمة المرور الجديدة</label> --}}
                </div>
                
                <button type="submit" class="btn btn-modern w-100 text-white">
                    <i class="fas fa-save me-2"></i>
                    إعادة تعيين كلمة المرور
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
    // Password strength checker
    const passwordInput = document.getElementById('password');
    const strengthDiv = document.querySelector('.password-strength');
    const progressBar = document.querySelector('.progress-bar');
    const strengthText = document.getElementById('strengthText');
    
    passwordInput?.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length > 0) {
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            let message = '';
            let color = '';
            
            // Check length
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Check for numbers
            if (/\d/.test(password)) strength++;
            
            // Check for lowercase letters
            if (/[a-z]/.test(password)) strength++;
            
            // Check for uppercase letters
            if (/[A-Z]/.test(password)) strength++;
            
            // Check for special characters
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            
            // Determine strength level (max 6)
            if (strength <= 2) {
                message = 'ضعيفة جداً';
                color = '#dc3545';
                progressBar.style.width = '25%';
            } else if (strength <= 4) {
                message = 'متوسطة';
                color = '#ffc107';
                progressBar.style.width = '50%';
            } else if (strength <= 5) {
                message = 'جيدة';
                color = '#17a2b8';
                progressBar.style.width = '75%';
            } else {
                message = 'قوية جداً';
                color = '#28a745';
                progressBar.style.width = '100%';
            }
            
            progressBar.style.backgroundColor = color;
            strengthText.textContent = `قوة كلمة المرور: ${message}`;
            strengthText.style.color = color;
        } else {
            strengthDiv.style.display = 'none';
        }
    });
    
    document.getElementById('resetForm')?.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;
        
        if (password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'كلمة المرور وتأكيدها غير متطابقين',
                confirmButtonColor: '#F6BE00'
            });
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                confirmButtonColor: '#F6BE00'
            });
            return false;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري التعيين...';
        submitBtn.disabled = true;
    });
</script>
@endpush