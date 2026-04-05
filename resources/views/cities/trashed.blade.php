{{-- resources/views/cities/trashed.blade.php --}}

@extends('layouts.app')

@section('page-title', 'سلة المحذوفات - المدن')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-trash-alt text-warning"></i> سلة المحذوفات - المدن
            </h2>
            <div>
                <a href="{{ route('cities.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> العودة للمدن
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-warning bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-info-circle"></i>
                        المدن المحذوفة مؤقتاً - يمكنك استعادتها أو حذفها نهائياً
                    </span>
                    <span class="badge bg-danger">
                        عدد المحذوفات: {{ $cities->total() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($cities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>اسم المدينة</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>تاريخ الحذف</th>
                                    <th width="300">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cities as $city)
                                    <tr>
                                        <td>{{ $city->id }}</td>
                                        <td>
                                            {{ $city->name }}
                                            <span class="badge bg-danger ms-2">محذوفة</span>
                                        </td>
                                        <td>{{ $city->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <i class="fas fa-clock text-warning"></i>
                                            {{ $city->deleted_at->format('Y-m-d H:i') }}
                                            <small class="text-muted">({{ $city->deleted_at->diffForHumans() }})</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- Restore button --}}
                                                <button type="button" class="btn btn-sm btn-success restore-btn"
                                                    data-id="{{ $city->id }}" data-name="{{ $city->name }}">
                                                    <i class="fas fa-undo"></i> استعادة
                                                </button>

                                                {{-- Force delete button (permanent) --}}
                                                <button type="button" class="btn btn-sm btn-danger force-delete-btn"
                                                    data-id="{{ $city->id }}" data-name="{{ $city->name }}">
                                                    <i class="fas fa-trash-alt"></i> حذف نهائي
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-trash-alt fa-3x mb-3 d-block text-warning"></i>
                                            <h5>لا توجد مدن محذوفة</h5>
                                            <p class="text-muted">سلة المحذوفات فارغة</p>
                                            <a href="{{ route('cities.index') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-arrow-right"></i> العودة إلى المدن
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $cities->links() }}
                    </div>

                    {{-- Bulk actions (optional) --}}
                    @if($cities->count() > 0)
                        <div class="mt-3 pt-3 border-top">
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb"></i>
                                <strong>ملاحظة:</strong> يمكنك استعادة أي مدينة محذوفة، أو حذفها نهائياً إذا كنت متأكداً من عدم
                                الحاجة إليها.
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-trash-alt fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">سلة المحذوفات فارغة</h5>
                        <p class="text-muted">لا توجد مدن محذوفة حالياً</p>
                        <a href="{{ route('cities.index') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-arrow-right"></i> العودة إلى المدن
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
                const cityId = $(this).data('id');
                const cityName = $(this).data('name');

                Swal.fire({
                    title: 'استعادة المدينة',
                    html: `هل تريد استعادة مدينة <strong class="text-success">${cityName}</strong>؟`,
                    text: "سيتم نقل المدينة إلى قائمة المدن النشطة",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، استعادة',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'جاري الاستعادة...',
                            text: 'يرجى الانتظار',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit restore request
                        $.ajax({
                            url: `/cities/${cityId}/restore`,
                            type: 'PUT',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الاستعادة',
                                    text: response.message || 'تم استعادة المدينة بنجاح',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                let errorMessage = 'حدث خطأ أثناء استعادة المدينة';
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

            // Force delete button handler (permanent delete)
            $('.force-delete-btn').on('click', function () {
                const cityId = $(this).data('id');
                const cityName = $(this).data('name');

                Swal.fire({
                    title: 'حذف نهائي',
                    html: `هل أنت متأكد من الحذف النهائي لمدينة <strong class="text-danger">${cityName}</strong>؟`,
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
                            url: `/cities/${cityId}/force-delete`,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف',
                                    text: response.message || 'تم حذف المدينة نهائياً',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                let errorMessage = 'لا يمكن حذف المدينة نهائياً لوجود طلبات مرتبطة بها';
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

            // Add keyboard shortcuts (optional)
            $(document).keydown(function (e) {
                // Press 'R' to restore (when a restore button is focused)
                if (e.key === 'r' || e.key === 'R') {
                    if ($('.restore-btn:focus').length) {
                        $('.restore-btn:focus').click();
                    }
                }
                // Press 'D' for force delete (when a delete button is focused)
                if (e.key === 'd' || e.key === 'D') {
                    if ($('.force-delete-btn:focus').length) {
                        $('.force-delete-btn:focus').click();
                    }
                }
            });

        });
    </script>
@endpush