{{-- resources/views/drivers/index.blade.php --}}

@extends('layouts.app')

@section('page-title', 'السائقين')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="fas fa-users"></i> السائقين
                </h2>
            </div>
            <div>
                @php 
                    $trashedCount = \App\Models\Driver::onlyTrashed()->count();
                @endphp
                <a href="{{ route('drivers.trashed') }}" class="btn btn-warning me-2">
                    <i class="fas fa-trash-alt"></i> سلة المحذوفات
                    @if($trashedCount > 0)
                        <span class="badge bg-danger ms-1">{{ $trashedCount }}</span>
                    @endif
                </a>
                
                <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة سائق
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                @if($drivers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>اسم السائق</th>
                                    <th>عدد طلبات اليوم</th>
                                    <th>ملاحظات</th>
                                    <th>تاريخ الإضافة</th>
                                    <th width="450">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drivers as $driver)
                                    <tr>
                                        <td>{{ $drivers->firstItem() + $loop->index }}</td>
                                        <td>{{ $driver->name }}</td>
                                        <td>
                                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
                                                {{ $driver->orders->where('created_at', '>=', now()->startOfDay())->count() }}
                                            </span>
                                        </td>
                                        <td>{{ $driver->notes ?? '—' }}</td>
                                        <td>{{ $driver->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('drivers.orders', $driver) }}"
                                                    class="btn btn-sm btn-outline-success" title="عرض الطلبات">
                                                    <i class="fas fa-box"></i> الطلبات
                                                </a>
                                                <a href="{{ route('drivers.attach-orders', $driver) }}"
                                                    class="btn btn-sm btn-outline-primary" title="إسناد طلبات">
                                                    <i class="fas fa-link"></i> إسناد
                                                </a>
                                                <a href="{{ route('drivers.edit', $driver) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="تعديل">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-id="{{ $driver->id }}"
                                                    data-name="{{ $driver->name }}"
                                                    title="حذف">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </div>
                                         </td>
                                     </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            لا يوجد سائقين مسجلين
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $drivers->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا يوجد سائقين مسجلين</h5>
                        <a href="{{ route('drivers.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus"></i> إضافة سائق جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .gap-2 {
        gap: 0.5rem;
    }
    .btn-sm {
        padding: 0.4rem 0.8rem;
        min-width: 80px;
    }
    .table td {
        vertical-align: middle;
    }
    .d-flex {
        display: flex;
        flex-wrap: wrap;
    }
    .badge {
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    
    // Delete button handler
    $('.delete-btn').on('click', function() {
        const driverId = $(this).data('id');
        const driverName = $(this).data('name');
        
        Swal.fire({
            title: 'هل أنت متأكد؟',
            html: `هل تريد حذف السائق <strong>${driverName}</strong>؟`,
            text: "سيتم نقل السائق إلى سلة المحذوفات",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'جاري الحذف...',
                    text: 'يرجى الانتظار',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: `/drivers/${driverId}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الحذف',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'حدث خطأ أثناء حذف السائق';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: errorMessage,
                            confirmButtonText: 'حسنًا'
                        });
                    }
                });
            }
        });
    });
    
});
</script>
@endpush