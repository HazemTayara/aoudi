{{-- resources/views/orders/trashed.blade.php --}}

@extends('layouts.app')

@section('page-title', 'سلة المحذوفات - الطلبات')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-warning bg-opacity-10 me-3">
                    <i class="fas fa-trash-alt text-warning fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">سلة المحذوفات - الطلبات</h2>
                    <p class="text-muted mb-0">عرض وإدارة الطلبات المحذوفة مؤقتاً</p>
                </div>
            </div>


            <div>
                <a href="{{ route('menafests.orders.index', $menafest) }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> العودة للطلبات
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

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle text-warning"></i>
                        <span class="me-2">الطلبات المحذوفة مؤقتاً - يمكنك استعادتها أو حذفها نهائياً</span>
                    </div>
                    <span class="badge bg-danger">
                        عدد المحذوفات: {{ $orders->total() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>رقم الإيصال</th>
                                    <th>المنافست</th>
                                    <th>المحتوى</th>
                                    <th>العدد</th>
                                    <th>المرسل</th>
                                    <th>المرسل إليه</th>
                                    <th>نوع الدفع</th>
                                    <th>المبلغ</th>
                                    <th>تم الاستلام</th>
                                    <th>السائق</th>
                                    <th>تاريخ الحذف</th>
                                    <th width="250">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr id="order-row-{{ $order->id }}">
                                        <td>
                                            <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                                {{ $orders->firstItem() + $loop->index }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ $order->order_number }}</td>
                                        <td>
                                            @if($order->menafest)
                                                <small>
                                                    {{ $order->menafest->manafest_code }}
                                                    <br>
                                                    <span class="text-muted">{{ $order->menafest->fromCity->name ?? '—' }}</span>
                                                </small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $order->content ?? '—' }}</td>
                                        <td>{{ format_number($order->count) }}</td>
                                        <td>{{ $order->sender ?? '—' }}</td>
                                        <td>{{ $order->recipient ?? '—' }}</td>
                                        <td>
                                            @if($order->pay_type == 'تحصيل')
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">تحصيل</span>
                                            @else
                                                <span class="badge bg-success text-white px-3 py-2 rounded-pill">مسبق</span>
                                            @endif
                                        </td>
                                        <td>{{ format_number($order->amount) }}</td>
                                        <td>
                                            @if($order->is_paid)
                                                <span class="badge bg-success">مدفوع</span>
                                            @else
                                                <span class="badge bg-danger">غير مدفوع</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->driver)
                                                <small>{{ $order->driver->name }}</small>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fas fa-clock text-warning"></i>
                                            {{ $order->deleted_at->format('Y-m-d H:i') }}
                                            <br>
                                            <small class="text-muted">({{ $order->deleted_at->diffForHumans() }})</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- Restore button --}}
                                                <button type="button" class="btn btn-sm btn-success restore-btn"
                                                    data-id="{{ $order->id }}" data-number="{{ $order->order_number }}">
                                                    <i class="fas fa-undo"></i> استعادة
                                                </button>

                                                {{-- Force delete button --}}
                                                <button type="button" class="btn btn-sm btn-danger force-delete-btn"
                                                    data-id="{{ $order->id }}" data-number="{{ $order->order_number }}">
                                                    <i class="fas fa-trash-alt"></i> حذف نهائي
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-5">
                                            <i class="fas fa-trash-alt fa-3x mb-3 d-block text-warning"></i>
                                            <h5 class="text-muted">لا توجد طلبات محذوفة</h5>
                                            <p class="text-muted">سلة المحذوفات فارغة</p>
                                            <a href="{{ route('manage-orders.index') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-arrow-right"></i> العودة للطلبات
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3 p-3">
                        {{ $orders->links() }}
                    </div>

                    @if($orders->count() > 0)
                        <div class="alert alert-info m-3">
                            <i class="fas fa-lightbulb"></i>
                            <strong>ملاحظة:</strong> يمكنك استعادة أي طلب محذوف، أو حذفه نهائياً إذا كنت متأكداً من عدم الحاجة إليه.
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-trash-alt fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">سلة المحذوفات فارغة</h5>
                        <p class="text-muted">لا توجد طلبات محذوفة حالياً</p>
                        <a href="{{ route('manage-orders.index') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-arrow-right"></i> العودة للطلبات
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            min-width: 100px;
        }

        .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        .d-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .badge {
            font-size: 0.85rem;
            font-weight: 500;
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
                const orderId = $(this).data('id');
                const orderNumber = $(this).data('number');

                Swal.fire({
                    title: 'استعادة الطلب',
                    html: `هل تريد استعادة الطلب رقم <strong class="text-success">${orderNumber}</strong>؟`,
                    text: "سيتم نقل الطلب إلى قائمة الطلبات النشطة",
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
                            url: `/orders/${orderId}/restore`,
                            type: 'PUT',
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
                                let errorMessage = 'حدث خطأ أثناء استعادة الطلب';
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
                const orderId = $(this).data('id');
                const orderNumber = $(this).data('number');

                Swal.fire({
                    title: 'حذف نهائي',
                    html: `هل أنت متأكد من الحذف النهائي للطلب رقم <strong class="text-danger">${orderNumber}</strong>؟`,
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
                            url: `/orders/${orderId}/force-delete`,
                            type: 'DELETE',
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
                                let errorMessage = 'لا يمكن حذف الطلب نهائياً';
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