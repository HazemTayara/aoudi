{{-- resources/views/menafests/trashed.blade.php --}}

@extends('layouts.app')

@section('page-title', $pageTitle)

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="fas fa-trash-alt text-warning"></i>
                    {{ $pageTitle }}
                </h2>
                <p class="text-muted mb-0">عرض وإدارة المنافست المحذوفة مؤقتاً</p>
            </div>
            <div>
                @if($type == 'incoming')
                    <a href="{{ route('menafests.incoming') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> العودة للمنافست الواردة
                    </a>
                @else
                    <a href="{{ route('menafests.outgoing') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> العودة للمنافست الصادرة
                    </a>
                @endif
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
                    <div>
                        <i class="fas fa-info-circle"></i>
                        <span class="me-2">المنافست المحذوفة مؤقتاً - يمكنك استعادتها أو حذفها نهائياً</span>
                    </div>
                    <span class="badge bg-danger">
                        عدد المحذوفات: {{ $menafests->total() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($menafests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>كود المنفست</th>
                                    @if($type == 'incoming')
                                        <th>المدينة المصدر</th>
                                    @else
                                        <th>مدينة الوجهة</th>
                                    @endif
                                    <th>اسم السائق</th>
                                    <th>السيارة</th>
                                    <th>عدد الطلبات</th>
                                    <th>ملاحظات</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>تاريخ الحذف</th>
                                    <th width="250">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menafests as $menafest)
                                    <tr>
                                        <td>{{ $menafests->firstItem() + $loop->index }}</td>
                                        <td>
                                            {{ $menafest->manafest_code }}
                                            <span class="badge bg-danger ms-2">محذوف</span>
                                        </td>
                                        @if($type == 'incoming')
                                            <td>{{ $menafest->fromCity->name ?? '—' }}</td>
                                        @else
                                            <td>{{ $menafest->toCity->name ?? '—' }}</td>
                                        @endif
                                        <td>{{ $menafest->driver_name }}</td>
                                        <td>{{ $menafest->car }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $menafest->orders->count() }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($menafest->notes, 30) ?? '—' }}</td>
                                        <td>{{ $menafest->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <i class="fas fa-clock text-warning"></i>
                                            {{ $menafest->deleted_at->format('Y-m-d H:i') }}
                                            <br>
                                            <small class="text-muted">({{ $menafest->deleted_at->diffForHumans() }})</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- Restore button --}}
                                                <button type="button" class="btn btn-sm btn-success restore-btn"
                                                    data-id="{{ $menafest->id }}" data-code="{{ $menafest->manafest_code }}"
                                                    data-type="{{ $type }}">
                                                    <i class="fas fa-undo"></i> استعادة
                                                </button>

                                                {{-- Force delete button --}}
                                                <button type="button" class="btn btn-sm btn-danger force-delete-btn"
                                                    data-id="{{ $menafest->id }}" data-code="{{ $menafest->manafest_code }}"
                                                    data-type="{{ $type }}">
                                                    <i class="fas fa-trash-alt"></i> حذف نهائي
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="fas fa-trash-alt fa-3x mb-3 d-block text-warning"></i>
                                            <h5 class="text-muted">لا توجد منافست محذوفة</h5>
                                            <p class="text-muted">سلة المحذوفات فارغة</p>
                                            @if($type == 'incoming')
                                                <a href="{{ route('menafests.incoming') }}" class="btn btn-primary mt-2">
                                                    <i class="fas fa-arrow-right"></i> العودة للمنافست الواردة
                                                </a>
                                            @else
                                                <a href="{{ route('menafests.outgoing') }}" class="btn btn-primary mt-2">
                                                    <i class="fas fa-arrow-right"></i> العودة للمنافست الصادرة
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $menafests->appends(request()->query())->links() }}
                    </div>

                    @if($menafests->count() > 0)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-lightbulb"></i>
                            <strong>ملاحظة:</strong>
                            @if($type == 'incoming')
                                يمكنك استعادة أي منفست وارد محذوف، أو حذفه نهائياً إذا كنت متأكداً من عدم الحاجة إليه.
                            @else
                                يمكنك استعادة أي منفست صادر محذوف، أو حذفه نهائياً إذا كنت متأكداً من عدم الحاجة إليه.
                            @endif
                            ملاحظة: لا يمكن حذف المنفست نهائياً إذا كان يحتوي على طلبات.
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-trash-alt fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">سلة المحذوفات فارغة</h5>
                        <p class="text-muted">لا توجد منافست محذوفة حالياً</p>
                        @if($type == 'incoming')
                            <a href="{{ route('menafests.incoming') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-arrow-right"></i> العودة للمنافست الواردة
                            </a>
                        @else
                            <a href="{{ route('menafests.outgoing') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-arrow-right"></i> العودة للمنافست الصادرة
                            </a>
                        @endif
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

        /* Pagination Styles */
        .pagination {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 0.9rem;
            background: white;
            color: #4a5568;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            border: 2px solid #e2e8f0;
            cursor: pointer;
        }

        .page-link:hover {
            background: var(--primary-light);
            color: #4a5568;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .page-item.active .page-link {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(246, 190, 0, 0.3);
            font-weight: 700;
        }

        .page-item.disabled .page-link {
            background: #f7fafc;
            color: #a0aec0;
            border-color: #e2e8f0;
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .page-link {
                min-width: 38px;
                height: 38px;
                padding: 0 0.7rem;
                font-size: 0.9rem;
                border-radius: 12px;
            }

            .btn-sm {
                min-width: 80px;
                font-size: 0.75rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Restore button handler
            $('.restore-btn').on('click', function () {
                const menafestId = $(this).data('id');
                const menafestCode = $(this).data('code');
                const type = $(this).data('type');

                Swal.fire({
                    title: 'استعادة المنفست',
                    html: `هل تريد استعادة المنفست <strong class="text-success">${menafestCode}</strong>؟`,
                    text: "سيتم نقل المنفست إلى القائمة النشطة",
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
                            url: `/menafests/${menafestId}/restore`,
                            type: 'PUT',
                            data: {
                                type: type
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الاستعادة',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Redirect back to the appropriate trashed page
                                    if (type === 'incoming') {
                                        window.location.href = '{{ route("menafests.incoming.trashed") }}';
                                    } else {
                                        window.location.href = '{{ route("menafests.outgoing.trashed") }}';
                                    }
                                });
                            },
                            error: function (xhr) {
                                let errorMessage = 'حدث خطأ أثناء استعادة المنفست';
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

            // Force delete button handler
            $('.force-delete-btn').on('click', function () {
                const menafestId = $(this).data('id');
                const menafestCode = $(this).data('code');
                const type = $(this).data('type');

                // Check if manifest has orders
                const hasOrders = $(this).closest('tr').find('.badge.bg-primary').text();

                Swal.fire({
                    title: 'حذف نهائي',
                    html: `هل أنت متأكد من الحذف النهائي للمنفست <strong class="text-danger">${menafestCode}</strong>؟`,
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
                            url: `/menafests/${menafestId}/force-delete`,
                            type: 'DELETE',
                            data: {
                                type: type
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Redirect back to the appropriate trashed page
                                    if (type === 'incoming') {
                                        window.location.href = '{{ route("menafests.incoming.trashed") }}';
                                    } else {
                                        window.location.href = '{{ route("menafests.outgoing.trashed") }}';
                                    }
                                });
                            },
                            error: function (xhr) {
                                let errorMessage = 'لا يمكن حذف المنفست نهائياً لوجود طلبات مرتبطة به';
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

            // Add keyboard shortcuts
            $(document).keydown(function (e) {
                if (e.key === 'r' || e.key === 'R') {
                    if ($('.restore-btn:focus').length) {
                        $('.restore-btn:focus').click();
                    }
                }
                if (e.key === 'd' || e.key === 'D') {
                    if ($('.force-delete-btn:focus').length) {
                        $('.force-delete-btn:focus').click();
                    }
                }
            });
        });
    </script>
@endpush