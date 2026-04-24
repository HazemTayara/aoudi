@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-primary-light me-3">
                    <i class="fas fa-user-tie text-primary fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">طلبات السائق: {{ $driver->name }}</h2>
                    <p class="text-muted mb-0">إدارة الطلبات المسندة لهذا السائق</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('drivers.attach-orders', $driver) }}" class="btn btn-success btn-lg rounded-pill px-4">
                    <i class="fas fa-link me-2"></i>إسناد طلبات
                </a>
                <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                    <i class="fas fa-arrow-right me-2"></i>العودة
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <span class="stat-label-sm">إجمالي الطلبات</span>
                    <h4 class="stat-value-sm">{{ format_number($stats['total']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <span class="stat-label-sm">مدفوع</span>
                    <h4 class="stat-value-sm text-success">{{ format_number($stats['paid_count']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <span class="stat-label-sm">غير مدفوع</span>
                    <h4 class="stat-value-sm text-danger">{{ format_number($stats['unpaid_count']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <span class="stat-label-sm">موجود</span>
                    <h4 class="stat-value-sm text-info">{{ format_number($stats['exist_count']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <span class="stat-label-sm">إجمالي المبلغ</span>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_amount']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <span class="stat-label-sm">إجمالي مبلغ التحصيل</span>
                    <h4 class="stat-value-sm">{{ format_number($stats['collection_amount']) }}</h4>
                </div>
            </div>
        </div>

        <!-- Server-side Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-filter text-primary ms-2"></i>بحث وفلترة</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.orders', $driver) }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">رقم الإيصال</label>
                            <input type="text" name="order_number" class="form-control"
                                value="{{ request('order_number') }}" placeholder="بحث...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">المرسل</label>
                            <input type="text" name="sender" class="form-control" value="{{ request('sender') }}"
                                placeholder="بحث...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">المرسل إليه</label>
                            <input type="text" name="recipient" class="form-control" value="{{ request('recipient') }}"
                                placeholder="بحث...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">نوع الدفع</label>
                            <select name="pay_type" class="form-control">
                                <option value="">الكل</option>
                                <option value="تحصيل" {{ request('pay_type') == 'تحصيل' ? 'selected' : '' }}>تحصيل</option>
                                <option value="مسبق" {{ request('pay_type') == 'مسبق' ? 'selected' : '' }}>مسبق</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تاريخ الإسناد من</label>
                            <input type="date" name="assigned_from" class="form-control"
                                value="{{ request('assigned_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تاريخ الإسناد إلى</label>
                            <input type="date" name="assigned_to" class="form-control" value="{{ request('assigned_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تاريخ الإنشاء من</label>
                            <input type="date" name="created_from" class="form-control"
                                value="{{ request('created_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تاريخ الإنشاء إلى</label>
                            <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تاريخ الدفع من</label>
                            <input type="date" name="paid_from" class="form-control" value="{{ request('paid_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تاريخ الدفع إلى</label>
                            <input type="date" name="paid_to" class="form-control" value="{{ request('paid_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">تم الاستلام</label>
                            <select name="is_paid" class="form-control">
                                <option value="">الكل</option>
                                <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>نعم</option>
                                <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>لا</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">موجود</label>
                            <select name="is_exist" class="form-control">
                                <option value="">الكل</option>
                                <option value="1" {{ request('is_exist') === '1' ? 'selected' : '' }}>نعم</option>
                                <option value="0" {{ request('is_exist') === '0' ? 'selected' : '' }}>لا</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('drivers.orders', $driver) }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-redo me-2"></i>إعادة ضبط
                            </a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-search me-2"></i>بحث
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>رقم الإيصال</th>
                                <th>المنافست</th>
                                <th>المرسل</th>
                                <th>المرسل إليه</th>
                                <th>نوع الدفع</th>
                                <th>المبلغ</th>
                                <th>الكمية</th>
                                <th>تم الاستلام</th>
                                <th>موجود</th>
                                <th>تاريخ الإسناد</th>
                                <th style="min-width: 200px;">ملاحظات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr id="order-row-{{ $order->id }}">
                                    <td>
                                        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                                            {{ $orders->firstItem() + $loop->index }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">{{ $order->order_number }}</td>
                                    <td>
                                        @if($order->menafest)
                                            <small class="text-muted">{{ $order->menafest->manafest_code }} |
                                                {{ $order->menafest->fromCity->name }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $order->sender }}</td>
                                    <td>{{ $order->recipient }}</td>
                                    <td>
                                        @if($order->pay_type == 'تحصيل')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">تحصيل</span>
                                        @else
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill">مسبق</span>
                                        @endif
                                    </td>
                                    <td>{{ format_number($order->amount) }}</td>
                                    <td>{{ format_number($order->count) }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-paid" type="checkbox"
                                                data-id="{{ $order->id }}" {{ $order->is_paid ? 'checked' : '' }}>
                                        </div>
                                        <small class="text-muted d-block paid-date-{{ $order->id }}">
                                            {{ $order->paid_at ? $order->paid_at->format('Y-m-d H:i') : '' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-exist" type="checkbox"
                                                data-id="{{ $order->id }}" {{ $order->is_exist ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $order->assigned_at ? $order->assigned_at->format('Y-m-d H:i') : '—' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm notes-group">
                                            <input type="text" class="form-control notes-input" data-id="{{ $order->id }}"
                                                data-original="{{ $order->notes }}" value="{{ $order->notes }}"
                                                placeholder="ملاحظات...">
                                            <button class="btn btn-outline-primary btn-save-notes d-none"
                                                data-id="{{ $order->id }}" title="حفظ">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle btn-detach"
                                            data-id="{{ $order->id }}" title="فك الإسناد">
                                            <i class="fas fa-unlink"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">لا توجد طلبات مسندة لهذا السائق</h5>
                                        <a href="{{ route('drivers.attach-orders', $driver) }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-link me-2"></i>إسناد طلبات الآن
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($orders->hasPages())
            <div class="pagination-container">
                <nav role="navigation" aria-label="Pagination Navigation">
                    <ul class="pagination">
                        {{-- Previous Page Link --}}
                        @if($orders->onFirstPage())
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link prev-next">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link prev-next" href="{{ $orders->previousPageUrl() }}" rel="prev">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @endif

                        {{-- First page with ellipsis logic --}}
                        @if($orders->currentPage() > 3)
                            <li class="page-item">
                                <a class="page-link" href="{{ $orders->url(1) }}">1</a>
                            </li>
                            @if($orders->currentPage() > 4)
                                <li class="page-item disabled">
                                    <span class="page-link ellipsis">•••</span>
                                </li>
                            @endif
                        @endif

                        {{-- Pages around current page --}}
                        @foreach(range(1, $orders->lastPage()) as $i)
                            @if($i >= $orders->currentPage() - 2 && $i <= $orders->currentPage() + 2)
                                @if($i == $orders->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link active-page">{{ $i }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $orders->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endif
                        @endforeach

                        {{-- Last page with ellipsis logic --}}
                        @if($orders->currentPage() < $orders->lastPage() - 2)
                            @if($orders->currentPage() < $orders->lastPage() - 3)
                                <li class="page-item disabled">
                                    <span class="page-link ellipsis">•••</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $orders->url($orders->lastPage()) }}">
                                    {{ $orders->lastPage() }}
                                </a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if($orders->hasMorePages())
                            <li class="page-item">
                                <a class="page-link prev-next" href="{{ $orders->nextPageUrl() }}" rel="next">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link prev-next">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
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
            background: var(--primary-light);
        }

        .stat-card-sm {
            background: white;
            border-radius: 16px;
            padding: 1rem 1.2rem;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.04);
            text-align: center;
        }

        .stat-label-sm {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .stat-value-sm {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0.25rem 0 0 0;
        }

        .bg-primary-light {
            background-color: var(--primary-light) !important;
        }

        .form-control,
        .btn {
            border-radius: 12px;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 2px solid var(--primary-light);
            padding: 1rem 0.75rem;
        }

        .table td {
            padding: 0.75rem;
            color: #2c3e50;
            vertical-align: middle;
        }

        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .notes-input {
            border: 1px solid #dee2e6;
            border-radius: 8px !important;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }

        .notes-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.15rem rgba(246, 190, 0, 0.25);
        }

        .notes-input.changed {
            border-color: var(--primary);
            background-color: #fffef5;
        }

        .notes-group .btn {
            border-radius: 0 8px 8px 0 !important;
        }

        /* Pagination Styles with Rounded Borders */
        :root {
            --pagination-radius: 14px;
            --pagination-glow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            --pagination-transition: all 0.2s ease;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            padding: 1rem 0;
        }

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

        /* Page Items */
        .page-item {
            margin: 0;
        }

        /* Page Links - Base Style */
        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 0.9rem;
            background: transparent;
            color: var(--heading-color);
            text-decoration: none;
            border-radius: var(--pagination-radius);
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--pagination-transition);
            border: 2px solid transparent;
            cursor: pointer;
        }

        /* Unselected Pages */
        .page-link:not(.active-page):not(.prev-next):not(.ellipsis) {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        /* Hover State for Unselected Pages */
        .page-link:not(.active-page):not(.prev-next):not(.ellipsis):hover {
            background: var(--accent-color);
            color: #4a5568;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(99, 102, 241, 0.25);
        }

        /* Active Page - Selected State */
        .page-item.active .page-link,
        .page-link.active-page {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(99, 102, 241, 0.3);
            font-weight: 700;
        }

        /* Previous/Next Buttons */
        .page-link.prev-next {
            background: white;
            border: 2px solid #e2e8f0;
            min-width: 42px;
            padding: 0;
            border-radius: var(--pagination-radius);
        }

        .page-link.prev-next:hover:not(.disabled .page-link) {
            transform: translateY(-2px);
        }

        /* Disabled State */
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

        /* Ellipsis Style */
        .page-link.ellipsis {
            background: transparent;
            border: none;
            color: #a0aec0;
            min-width: auto;
            padding: 0 0.25rem;
            font-size: 1.1rem;
            letter-spacing: 2px;
            cursor: default;
            pointer-events: none;
        }

        .page-link.ellipsis:hover {
            background: transparent;
            transform: none;
            box-shadow: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .pagination {
                gap: 0.35rem;
            }

            .page-link {
                min-width: 38px;
                height: 38px;
                padding: 0 0.7rem;
                font-size: 0.9rem;
                border-radius: 12px;
            }

            .page-link.prev-next {
                min-width: 38px;
            }
        }

        @media (max-width: 480px) {
            .pagination {
                gap: 0.25rem;
            }

            .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0 0.5rem;
                font-size: 0.85rem;
                border-radius: 10px;
            }
        }

        /* Focus State for Accessibility */
        .page-link:focus-visible {
            outline: none;
            box-shadow: var(--pagination-glow);
            border-color: var(--accent-color);
        }

        /* Selected page animation */
        .page-item.active .page-link {
            animation: pop 0.2s ease;
        }

        @keyframes pop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.08);
            }

            100% {
                transform: scale(1) translateY(-2px);
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

            // ─── Toggle is_paid ───
            $(document).on('change', '.toggle-paid', function () {
                let checkbox = $(this);
                let orderId = checkbox.data('id');
                let wasChecked = !checkbox.is(':checked');

                checkbox.prop('disabled', true);

                $.ajax({
                    url: '/manage-orders/' + orderId + '/toggle-paid',
                    type: 'PATCH',
                    success: function (response) {
                        if (response.success) {
                            let dateEl = $('.paid-date-' + orderId);
                            dateEl.text(response.paid_at || '');

                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message, showConfirmButton: false, timer: 1500
                            });
                        }
                    },
                    error: function () {
                        checkbox.prop('checked', wasChecked);
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'error',
                            title: 'حدث خطأ أثناء تحديث حالة الدفع', showConfirmButton: false, timer: 2000
                        });
                    },
                    complete: function () {
                        checkbox.prop('disabled', false);
                    }
                });
            });

            // ─── Toggle is_exist ───
            $(document).on('change', '.toggle-exist', function () {
                let checkbox = $(this);
                let orderId = checkbox.data('id');
                let wasChecked = !checkbox.is(':checked');

                checkbox.prop('disabled', true);

                $.ajax({
                    url: '/manage-orders/' + orderId + '/toggle-exist',
                    type: 'PATCH',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message, showConfirmButton: false, timer: 1500
                            });
                        }
                    },
                    error: function () {
                        checkbox.prop('checked', wasChecked);
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'error',
                            title: 'حدث خطأ أثناء تحديث حالة الوجود', showConfirmButton: false, timer: 2000
                        });
                    },
                    complete: function () {
                        checkbox.prop('disabled', false);
                    }
                });
            });

            // ─── Inline editable notes ───
            // Show save button when notes value changes
            $(document).on('input', '.notes-input', function () {
                let input = $(this);
                let orderId = input.data('id');
                let original = input.data('original') || '';
                let current = input.val();
                let saveBtn = input.siblings('.btn-save-notes');

                if (current !== original) {
                    input.addClass('changed');
                    saveBtn.removeClass('d-none');
                } else {
                    input.removeClass('changed');
                    saveBtn.addClass('d-none');
                }
            });

            // Save notes on button click
            $(document).on('click', '.btn-save-notes', function () {
                let btn = $(this);
                let orderId = btn.data('id');
                let input = btn.siblings('.notes-input');
                let newNotes = input.val();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '/manage-orders/' + orderId + '/update-notes',
                    type: 'PATCH',
                    data: { notes: newNotes },
                    success: function (response) {
                        if (response.success) {
                            input.data('original', newNotes);
                            input.removeClass('changed');
                            btn.addClass('d-none');

                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message, showConfirmButton: false, timer: 1500
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'error',
                            title: 'حدث خطأ أثناء حفظ الملاحظات', showConfirmButton: false, timer: 2000
                        });
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    }
                });
            });

            // Save notes on Enter key
            $(document).on('keypress', '.notes-input', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).siblings('.btn-save-notes').click();
                }
            });

            // ─── Detach order ───
            $(document).on('click', '.btn-detach', function () {
                let btn = $(this);
                let orderId = btn.data('id');

                Swal.fire({
                    title: 'فك إسناد الطلب؟',
                    text: 'هل أنت متأكد من فك إسناد هذا الطلب من السائق؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، فك الإسناد',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/drivers/{{ $driver->id }}/detach-order/' + orderId,
                            type: 'POST',
                            data: { _method: 'DELETE' },
                            success: function (response) {
                                if (response.success) {
                                    $('#order-row-' + orderId).fadeOut(300, function () {
                                        $(this).remove();
                                    });
                                    Swal.fire({
                                        toast: true, position: 'top-end', icon: 'success',
                                        title: response.message, showConfirmButton: false, timer: 1500
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    toast: true, position: 'top-end', icon: 'error',
                                    title: 'حدث خطأ أثناء فك الإسناد', showConfirmButton: false, timer: 2000
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush