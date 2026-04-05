{{-- resources/views/drivers/trashed.blade.php --}}

@extends('layouts.app')

@section('page-title', 'سلة المحذوفات - السائقين')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-trash-alt text-warning"></i> سلة المحذوفات - السائقين
            </h2>
            <div>
                <a href="{{ route('drivers.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> العودة للسائقين
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
            <div class="card-header bg-warning bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-info-circle"></i>
                        السائقين المحذوفين مؤقتاً - يمكنك استعادتهم أو حذفهم نهائياً
                    </span>
                    <span class="badge bg-danger">
                        عدد المحذوفات: {{ $drivers->total() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($drivers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>اسم السائق</th>
                                    <th>عدد الطلبات</th>
                                    <th>ملاحظات</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>تاريخ الحذف</th>
                                    <th width="300">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drivers as $driver)
                                    <tr>
                                        <td>{{ $driver->id }}</td>
                                        <td>
                                            {{ $driver->name }}
                                            <span class="badge bg-danger ms-2">محذوف</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $driver->orders()->count() }}
                                            </span>
                                        </td>
                                        <td>{{ $driver->notes ?? '—' }}</td>
                                        <td>{{ $driver->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <i class="fas fa-clock text-warning"></i>
                                            {{ $driver->deleted_at->format('Y-m-d H:i') }}
                                            <small class="text-muted">({{ $driver->deleted_at->diffForHumans() }})</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- Restore button --}}
                                                <button type="button" class="btn btn-sm btn-success restore-btn"
                                                    data-id="{{ $driver->id }}" data-name="{{ $driver->name }}">
                                                    <i class="fas fa-undo"></i> استعادة
                                                </button>

                                                {{-- Force delete button --}}
                                                <button type="button" class="btn btn-sm btn-danger force-delete-btn"
                                                    data-id="{{ $driver->id }}" data-name="{{ $driver->name }}">
                                                    <i class="fas fa-trash-alt"></i> حذف نهائي
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fas fa-trash-alt fa-3x mb-3 d-block text-warning"></i>
                                            <h5>لا يوجد سائقين محذوفين</h5>
                                            <p class="text-muted">سلة المحذوفات فارغة</p>
                                            <a href="{{ route('drivers.index') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-arrow-right"></i> العودة للسائقين
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $drivers->links() }}
                    </div>

                    @if($drivers->count() > 0)
                        <div class="mt-3 pt-3 border-top">
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb"></i>
                                <strong>ملاحظة:</strong> يمكنك استعادة أي سائق محذوف، أو حذفه نهائياً إذا كنت متأكداً من عدم الحاجة
                                إليه.
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-trash-alt fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">سلة المحذوفات فارغة</h5>
                        <p class="text-muted">لا يوجد سائقين محذوفين حالياً</p>
                        <a href="{{ route('drivers.index') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-arrow-right"></i> العودة للسائقين
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
            min-width: 100px;
        }

        .table td {
            vertical-align: middle;
        }

        .d-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {

            // Restore button handler
            $('.restore-btn').on('click', function () {
                const driverId = $(this).data('id');
                const driverName = $(this).data('name');

                Swal.fire({
                    title: 'استعادة السائق',
                    html: `هل تريد استعادة السائق <strong class="text-success">${driverName}</strong>؟`,
                    text: "سيتم نقل السائق إلى قائمة السائقين النشطين",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، استعادة',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'جاري الاستعادة...',
                            text: 'يرجى الانتظار',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `/drivers/${driverId}/restore`,
                            type: 'PUT',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الاستعادة',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطأ',
                                    text: 'حدث خطأ أثناء استعادة السائق',
                                    confirmButtonText: 'حسنًا'
                                });
                            }
                        });
                    }
                });
            });

            // Force delete button handler
            $('.force-delete-btn').on('click', function () {
                const driverId = $(this).data('id');
                const driverName = $(this).data('name');

                Swal.fire({
                    title: 'حذف نهائي',
                    html: `هل أنت متأكد من الحذف النهائي للسائق <strong class="text-danger">${driverName}</strong>؟`,
                    text: "هذا الإجراء لا يمكن التراجع عنه نهائياً!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذف نهائياً',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'جاري الحذف النهائي...',
                            text: 'يرجى الانتظار',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `/drivers/${driverId}/force-delete`,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
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
                            error: function (xhr) {
                                let errorMessage = 'لا يمكن حذف السائق نهائياً لوجود طلبات مرتبطة به';
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