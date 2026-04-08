{{-- resources/views/cities/index.blade.php --}}

@extends('layouts.app')

@section('page-title', 'المدن')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="fas fa-city"></i> المدن
                </h2>
            </div>
            <div>
                @permission('restore-cities')
                <a href="{{ route('cities.trashed') }}" class="btn btn-warning me-2">
                    <i class="fas fa-trash-alt"></i> سلة المحذوفات
                </a>
                @endpermission
                @permission('create-cities')
                <a href="{{ route('cities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة مدينة
                </a>
                @endpermission
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
            <div class="card-body">
                @if($cities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>اسم المدينة</th>
                                    <th>عدد طلبات اليوم</th>
                                    <th>تاريخ الإضافة</th>
                                    <th width="500">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cities as $city)
                                    <tr>
                                        <td>{{ $cities->firstItem() + $loop->index }}</td>
                                        <td>{{ $city->name }}</td>
                                        <td>
                                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
                                                {{ $city->orders->where('created_at', '>=', now()->startOfDay())->count() }}
                                            </span>
                                        </td>
                                        <td>{{ $city->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- Edit button --}}
                                                @permission('edit-cities')
                                                <a href="{{ route('cities.edit', $city) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                @endpermission

                                                {{-- View orders button --}}
                                                <a href="{{ route('cities.orders', $city) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-box"></i> عرض الطلبات
                                                </a>

                                                {{-- Delete button --}}
                                                @permission('delete-cities')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-id="{{ $city->id }}" data-name="{{ $city->name }}">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                                @endpermission
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            لا توجد مدن مسجلة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $cities->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-city fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مدن مسجلة</h5>
                        @permission('create-cities')
                        <a href="{{ route('cities.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus"></i> إضافة مدينة جديدة
                        </a>
                        @endpermission
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
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {

            // Delete button handler
            $('.delete-btn').on('click', function () {
                const cityId = $(this).data('id');
                const cityName = $(this).data('name');

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    html: `هل تريد حذف مدينة <strong>${cityName}</strong>؟`,
                    text: "سيتم نقل المدينة إلى سلة المحذوفات",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'جاري الحذف...',
                            text: 'يرجى الانتظار',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit delete request
                        $.ajax({
                            url: `/cities/${cityId}`,
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
                                let errorMessage = 'حدث خطأ أثناء حذف المدينة';
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