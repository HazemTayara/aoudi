@extends('layouts.app')
@section('content')
    <div class="container py-4">
        <div class="row justify-content-right">
            <div class="col-md-8">
                {{-- Profile Header --}}
                <div class="text-right mb-4">
                    <div class="avatar-circle mb-3 mx-auto">
                        <span class="avatar-initials">{{ substr($user->name, 0, 2) }}</span>
                    </div>
                    <h3 class="mb-1">{{ $user->name }}</h3>
                    <p class="text-muted">{{ $user->email }}</p>
                    <span class="badge bg-secondary"
                        style="font-size: 16px">{{ $user->roles->first()->display_name }}</span>
                </div>

                {{-- Profile Information Card --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle me-2"></i> معلومات الحساب
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">الاسم الكامل</label>
                                <p class="form-control-plaintext fw-semibold">{{ $user->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">البريد الإلكتروني</label>
                                <p class="form-control-plaintext fw-semibold">{{ $user->email }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">عضو منذ</label>
                                <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">آخر تحديث</label>
                                <p class="form-control-plaintext">{{ $user->updated_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Password Change Card --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i> تغيير كلمة المرور
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="passwordForm" method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-semibold">كلمة المرور الحالية</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control" placeholder="أدخل كلمة المرور الحالية">
                                </div>
                                <div class="invalid-feedback" id="error-current_password"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">كلمة المرور الجديدة</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" name="new_password" id="new_password" class="form-control"
                                        placeholder="********">
                                </div>
                                <div class="form-text small">
                                    <i class="bi bi-info-circle"></i>
                                    يجب أن تحتوي على 8 أحرف على الأقل، حرف كبير وصغير ورقم
                                </div>
                                <div class="invalid-feedback" id="error-new_password"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">تأكيد كلمة المرور الجديدة</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                        class="form-control" placeholder="أعد إدخال كلمة المرور الجديدة">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-arrow-repeat me-1"></i> إعادة تعيين
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                    <i class="bi bi-check-circle me-1"></i> تحديث كلمة المرور
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Alert Container for Messages --}}
                <div id="alertContainer"></div>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #F6BE00 0%, #e5a500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .avatar-initials {
            font-size: 32px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }

        .card {
            border-radius: 15px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .form-control-plaintext {
            font-size: 1rem;
            padding-top: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #F6BE00 0%, #e5a500 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #e5a500 0%, #d49400 100%);
            transform: translateY(-1px);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
        }

        /* Loading state */
        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('passwordForm');
            const submitBtn = document.getElementById('submitBtn');
            const alertContainer = document.getElementById('alertContainer');

            // Clear previous errors
            function clearErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                });
            }

            // Show error messages
            function showErrors(errors) {
                for (const [field, messages] of Object.entries(errors)) {
                    const input = document.querySelector(`[name="${field}"]`);
                    const errorDiv = document.getElementById(`error-${field}`);

                    if (input) {
                        input.classList.add('is-invalid');
                    }
                    if (errorDiv) {
                        errorDiv.textContent = messages[0];
                    }
                }
            }

            // Show alert message
            function showAlert(type, message) {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show mt-4 shadow-sm`;
                alert.role = 'alert';
                alert.innerHTML = `
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                alertContainer.appendChild(alert);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (alert && alert.remove) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }, 5000);
            }

            // Reset form
            function resetForm() {
                form.reset();
                clearErrors();
            }

            // Handle form submission
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                // Clear previous errors and alerts
                clearErrors();
                alertContainer.innerHTML = '';

                // Show loading state on button
                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> جاري التحديث...';
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;

                // Get form data
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Success - show success message
                        showAlert('success', data.message || 'تم تغيير كلمة المرور بنجاح!');
                        resetForm();
                    } else {
                        // Validation errors
                        if (data.errors) {
                            showErrors(data.errors);
                        } else if (data.message) {
                            showAlert('danger', data.message);
                        } else {
                            showAlert('danger', 'حدث خطأ ما. يرجى المحاولة مرة أخرى.');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('danger', 'حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.');
                } finally {
                    // Restore button state
                    submitBtn.innerHTML = originalBtnHtml;
                    submitBtn.classList.remove('btn-loading');
                    submitBtn.disabled = false;
                }
            });

            // Reset button clears errors
            document.querySelector('button[type="reset"]').addEventListener('click', function () {
                clearErrors();
                alertContainer.innerHTML = '';
            });
        });
    </script>
@endpush