@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="bi bi-people-fill me-2" style="color: #F6BE00;"></i>
                                    إدارة المستخدمين
                                </h4>
                                <p class="text-muted small mt-2">إدارة صلاحيات وحسابات المستخدمين</p>
                            </div>
                            @role('super-admin')
                            <a href="{{ route('users.create') }}" class="btn btn-primary px-4">
                                <i class="bi bi-person-plus-fill me-2"></i>
                                إضافة مستخدم جديد
                            </a>
                            @endrole
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Search and Filter --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="searchInput" class="form-control border-start-0"
                                        placeholder="بحث بالاسم أو البريد الإلكتروني...">
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <span class="badge bg-light text-dark p-2">
                                    <i class="bi bi-person-badge me-1"></i>
                                    إجمالي المستخدمين: {{ $users->total() }}
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-3">#</th>
                                        <th class="py-3">
                                            <i class="bi bi-person-circle me-1"></i> الاسم
                                        </th>
                                        <th class="py-3">
                                            <i class="bi bi-envelope me-1"></i> البريد الإلكتروني
                                        </th>
                                        <th class="py-3">
                                            <i class="bi bi-shield-shaded me-1"></i> الدور
                                        </th>
                                        @role('super-admin')
                                        <th class="py-3 text-center">
                                            <i class="bi bi-gear me-1"></i> الإجراءات
                                        </th>
                                        @endrole
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $index => $user)
                                        <tr>
                                            <td>{{ $users->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <span class="fw-semibold">{{ $user->name }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @php
                                                    $roleColors = [
                                                        'super-admin' => 'danger',
                                                        'admin' => 'warning',
                                                        'employee' => 'info'
                                                    ];
                                                    $role = $user->roles->first();
                                                    $roleColor = $roleColors[$role->name ?? ''] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} px-3 py-2">
                                                    <i class="bi bi-shield-check me-1"></i>
                                                    {{ $role->display_name ?? '-' }}
                                                </span>
                                            </td>
                                            @role('super-admin')
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('users.edit', $user) }}"
                                                        class="btn btn-sm btn-outline-warning">
                                                        تعديل
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                                        <i class="bi bi-trash"></i>
                                                        حذف
                                                    </button>
                                                </div>

                                                {{-- Delete Modal --}}
                                                <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="text-center">
                                                                    <i class="bi bi-exclamation-triangle-fill text-warning"
                                                                        style="font-size: 4rem;"></i>
                                                                    <p class="mt-3">هل أنت متأكد من حذف المستخدم
                                                                        <strong>{{ $user->name }}</strong>؟
                                                                    </p>
                                                                    <p class="text-danger small">لا يمكن التراجع عن هذا الإجراء!
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">إلغاء</button>
                                                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                                    class="d-inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            @endrole
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                <p class="mt-2 text-muted">لا يوجد مستخدمين</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #F6BE00 0%, #e5a500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-initials-sm {
            font-size: 16px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }

        .bg-warning-subtle {
            background-color: #fff3cd;
        }

        .bg-danger-subtle {
            background-color: #f8d7da;
        }

        .bg-info-subtle {
            background-color: #d1ecf1;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 0.75rem;
        }

        .btn-outline-warning:hover {
            background-color: #F6BE00;
            border-color: #F6BE00;
            color: white;
        }
    </style>

    <script>
        document.getElementById('searchInput')?.addEventListener('keyup', function () {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll('#usersTable tbody tr');

            rows.forEach(row => {
                let name = row.cells[1]?.textContent.toLowerCase() || '';
                let email = row.cells[2]?.textContent.toLowerCase() || '';
                let role = row.cells[3]?.textContent.toLowerCase() || '';

                if (name.includes(value) || email.includes(value) || role.includes(value)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection