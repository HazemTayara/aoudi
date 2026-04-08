@extends('layouts.app')
@section('content')
    <div>
        <div class="row justify-content-start">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('users.index') }}" class="text-decoration-none me-3">
                                <i class="bi bi-arrow-right-circle" style="font-size: 1.5rem; color: #F6BE00;"></i>
                            </a>
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="bi bi-person-plus-fill me-2" style="color: #F6BE00;"></i>
                                    إضافة مستخدم جديد
                                </h4>
                                <p class="text-muted small mt-2">قم بإدخال بيانات المستخدم الجديد</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('users.store') }}" id="userForm">
                            @csrf

                            {{-- Name Field --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-circle me-1"></i> الاسم الكامل
                                </label>
                                <input type="text" name="name"
                                    class="form-control form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="أدخل الاسم الكامل" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email Field --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1"></i> البريد الإلكتروني
                                </label>
                                <input type="email" name="email"
                                    class="form-control form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="example@domain.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password Field --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lock me-1"></i> كلمة المرور
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control form-control @error('password') is-invalid @enderror"
                                        placeholder="********" required>
                                </div>
                                <div class="form-text">
                                    <small>يجب أن تحتوي على 8 أحرف على الأقل، حرف كبير وصغير ورقم</small>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Confirm Password Field --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-1"></i> تأكيد كلمة المرور
                                </label>
                                <input type="password" name="password_confirmation" class="form-control form-control"
                                    placeholder="أعد إدخال كلمة المرور" required>
                            </div>

                            {{-- Role Field --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-shield-shaded me-1"></i> الدور الوظيفي
                                </label>
                                <select name="role" class="form-select form-select @error('role') is-invalid @enderror"
                                    required>
                                    <option value="">اختر الدور...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            @if($role->name == 'super-admin')
                                                <i class="bi bi-star-fill"></i>
                                            @elseif($role->name == 'admin')
                                                <i class="bi bi-shield-fill"></i>
                                            @else
                                                <i class="bi bi-person-badge"></i>
                                            @endif
                                            {{ $role->display_name }}
                                            <small class="text-muted">- {{ $role->description ?? '' }}</small>
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Form Actions --}}
                            <div class="d-flex justify-content-between gap-3 mt-4 pt-2">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn px-4">
                                    <i class="bi bi-x-circle me-2"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary btn px-5">
                                    <i class="bi bi-check-circle me-2"></i> إنشاء المستخدم
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Password Strength Indicator --}}
                <div class="card mt-3 shadow-sm border-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i> قوة كلمة المرور:
                            </small>
                            <div class="progress flex-grow-1 mx-3" style="height: 5px;">
                                <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="strengthText" class="text-muted">ضعيفة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-primary {
            background: linear-gradient(135deg, #F6BE00 0%, #e5a500 100%);
            border: none;
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #e5a500 0%, #d49400 100%);
            transform: translateY(-2px);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #F6BE00;
            box-shadow: 0 0 0 0.2rem rgba(246, 190, 0, 0.25);
        }
    </style>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        // Password strength indicator
        document.getElementById('password')?.addEventListener('input', function () {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';

            if (percentage <= 20) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'ضعيفة جداً';
            } else if (percentage <= 40) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'ضعيفة';
            } else if (percentage <= 60) {
                strengthBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'متوسطة';
            } else if (percentage <= 80) {
                strengthBar.className = 'progress-bar bg-primary';
                strengthText.textContent = 'قوية';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'قوية جداً';
            }
        });
    </script>
@endsection