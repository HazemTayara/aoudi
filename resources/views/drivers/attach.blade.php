@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-primary-light me-3">
                    <i class="fas fa-link text-primary fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">إسناد طلبات للسائق: {{ $driver->name }}</h2>
                    <p class="text-muted mb-0">اختر الطلبات التي تريد إسنادها لهذا السائق</p>
                </div>
            </div>
            <a href="{{ route('drivers.orders', $driver) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                <i class="fas fa-arrow-right me-2"></i>العودة لطلبات السائق
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-search text-primary ms-2"></i>بحث في الطلبات غير المسندة</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.attach-orders', $driver) }}">
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
                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('drivers.attach-orders', $driver) }}" class="btn btn-outline-secondary px-4">
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
        <form action="{{ route('drivers.attach-orders.store', $driver) }}" method="POST" id="attachForm">
            @csrf

            <!-- Sticky action bar -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <div>
                        <span class="fw-bold">الطلبات المتاحة: {{ $orders->total() }}</span>
                        <span class="text-muted me-3">|</span>
                        <span class="text-primary fw-bold" id="selectedCount">تم اختيار: 0</span>
                    </div>
                    <button type="submit" class="btn btn-success px-4 rounded-pill" id="attachBtn" disabled>
                        <i class="fas fa-link me-2"></i>إسناد المحدد
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>
                                        {{-- <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div> --}}
                                    </th>
                                    <th>#</th>
                                    <th>رقم الإيصال</th>
                                    <th>المنافست</th>
                                    <th>المرسل</th>
                                    <th>المرسل إليه</th>
                                    <th>نوع الدفع</th>
                                    <th>المبلغ</th>
                                    <th>ضد الشاحن</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input order-checkbox" type="checkbox"
                                                    name="order_ids[]" value="{{ $order->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                                                {{ $orders->firstItem() + $loop->index }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ $order->order_number }}</td>
                                        <td>
                                            @if($order->menafest)
                                                <small>
                                                    {{ $order->menafest->manafest_code }}
                                                    {{ ' | ' }}
                                                    <span class="text-muted">{{ $order->menafest->fromCity->name }}</span>
                                                </small>
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
                                        <td>{{ format_number($order->anti_charger) }}</td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 120px;"
                                                title="{{ $order->notes }}">
                                                {{ $order->notes ?? '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                                            <h5 class="text-muted">لا توجد طلبات متاحة للإسناد</h5>
                                            <p class="text-muted">جميع الطلبات غير المدفوعة والموجودة مسندة لسائقين</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>

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

        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        tr:has(.order-checkbox:checked) {
            background-color: #fffef0 !important;
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
            function updateSelectedCount() {
                let count = $('.order-checkbox:checked').length;
                $('#selectedCount').text('تم اختيار: ' + count);
                $('#attachBtn').prop('disabled', count === 0);
            }

            // Select all checkbox
            // $('#selectAll').on('change', function () {
            //     $('.order-checkbox').prop('checked', $(this).is(':checked'));
            //     updateSelectedCount();
            // });

            // Individual checkbox
            $(document).on('change', '.order-checkbox', function () {
                let total = $('.order-checkbox').length;
                let checked = $('.order-checkbox:checked').length;
                // $('#selectAll').prop('checked', total === checked);
                updateSelectedCount();
            });

            // Confirm before submit
            $('#attachForm').on('submit', function (e) {
                let count = $('.order-checkbox:checked').length;
                if (count === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'لم يتم اختيار طلبات',
                        text: 'يرجى اختيار طلب واحد على الأقل',
                    });
                    return false;
                }
            });
        });
    </script>
@endpush