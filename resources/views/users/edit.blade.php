@extends('layouts.app')
@section('content')
<div class="container py-5">
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
                                <i class="bi bi-pencil-square me-2" style="color: #F6BE00;"></i>
                                تعديل بيانات المستخدم
                            </h4>
                            <p class="text-muted small mt-2">تحديث معلومات وصلاحيات {{ $user->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- User Info Banner --}}
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md me-3">
                                <span class="avatar-initials-md">{{ substr($user->name, 0, 2) }}</span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $user->name }}</h6>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('users.update', $user) }}" id="userForm">
                        @csrf
                        @method('PUT')

                        {{-- Name Field --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-circle me-1"></i> الاسم الكامل
                            </label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email Field --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i> البريد الإلكتروني
                            </label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password Field (Optional) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-lock me-1"></i> كلمة المرور الجديدة
                                <span class="text-muted small">(اختياري)</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password"
                                       class="form-control form-control @error('password') is-invalid @enderror" 
                                       placeholder="اتركه فارغاً إذا لم تريد التغيير">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <small>اتركه فارغاً إذا لم ترغب في تغيير كلمة المرور</small>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirm Password Field --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-lock-fill me-1"></i> تأكيد كلمة المرور الجديدة
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="form-control form-control"
                                   placeholder="أعد إدخال كلمة المرور الجديدة">
                        </div>

                        {{-- Role Field --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-shield-shaded me-1"></i> الدور الوظيفي
                            </label>
                            <select name="role" class="form-select form-select @error('role') is-invalid @enderror" required>
                                <option value="">اختر الدور...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" 
                                        {{ old('role', $userRole->name ?? '') == $role->name ? 'selected' : '' }}>
                                        @if($role->name == 'super-admin')
                                            👑 
                                        @elseif($role->name == 'admin')
                                            🛡️ 
                                        @else
                                            👤 
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
                                <i class="bi bi-arrow-right me-2"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary btn px-5">
                                <i class="bi bi-save me-2"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-md {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #F6BE00 0%, #e5a500 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-initials-md {
        font-size: 24px;
        font-weight: bold;
        color: white;
        text-transform: uppercase;
    }
    .btn-primary {
        background: linear-gradient(135deg, #F6BE00 0%, #e5a500 100%);
        border: none;
        transition: transform 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #e5a500 0%, #d49400 100%);
        transform: translateY(-2px);
    }
    .form-control:focus, .form-select:focus {
        border-color: #F6BE00;
        box-shadow: 0 0 0 0.2rem rgba(246, 190, 0, 0.25);
    }
</style>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.querySelector('i').classList.toggle('bi-eye');
    this.querySelector('i').classList.toggle('bi-eye-slash');
});

// Warn if trying to change own role
const roleSelect = document.querySelector('select[name="role"]');
const currentUserRole = "{{ $userRole->name ?? '' }}";
const loggedInUserId = {{ auth()->id() }};
const editingUserId = {{ $user->id }};

if (loggedInUserId === editingUserId && roleSelect) {
    roleSelect.addEventListener('change', function() {
        if (this.value !== currentUserRole) {
            if (!confirm('⚠️ تحذير: أنت تقوم بتغيير دورك الخاص. قد تفقد بعض الصلاحيات. هل أنت متأكد؟')) {
                this.value = currentUserRole;
            }
        }
    });
}
</script>
@endsection