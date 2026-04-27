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
                    <h2 class="fw-bold mb-1">
                        إسناد طلبات للسائق: {{ $driver->name }}
                    </h2>
                    <p class="text-muted mb-0">ابحث عن الطلبات لإسنادها لهذا السائق</p>
                </div>
            </div>
            <a href="{{ route('drivers.orders', $driver) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                <i class="fas fa-arrow-right me-2"></i>العودة لطلبات السائق
            </a>
        </div>

        {{-- Toast Notification Container --}}
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999;">
            <div id="toastNotification" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="toastMessage"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-search text-primary ms-2"></i>بحث عن طلبات للإسناد</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.attach-orders', $driver) }}" id="searchForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-10">
                            <label class="form-label fw-bold small">بحث</label>
                            <input type="text" name="search" class="form-control form-control-lg"
                                value="{{ request('search') }}" 
                                placeholder="أدخل رقم الإيصال أو اسم المرسل إليه..." autofocus id="searchInput">
                            <small class="text-muted">يمكنك البحث برقم الإيصال أو اسم المرسل إليه | اضغط <kbd>Esc</kbd> لبحث جديد</small>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <a href="{{ route('drivers.attach-orders', $driver) }}" class="btn btn-outline-secondary flex-grow-1">
                                <i class="fas fa-redo"></i>
                            </a>
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(!$hasSearch)
            {{-- No search performed yet --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">ابحث عن الطلبات للإسناد</h5>
                    <p class="text-muted">أدخل رقم الإيصال أو اسم المرسل إليه في حقل البحث أعلاه</p>
                </div>
            </div>
        @elseif($orders->isEmpty())
            {{-- No results found --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد طلبات تطابق بحثك</h5>
                    <p class="text-muted">حاول تعديل معايير البحث أو استخدم كلمة بحث مختلفة</p>
                </div>
            </div>
        @elseif($orders->count() === 1 && $orders->total() === 1)
            {{-- Single result - show detailed form --}}
            @php 
                $order = $orders->first();
                $canAttach = true;
                $disableReason = '';
                
                if ($order->is_paid) {
                    $canAttach = false;
                    $disableReason = 'الطلب مدفوع ولا يمكن إسناده';
                } elseif ($order->driver_id && $order->driver_id == $driver->id) {
                    $canAttach = false;
                    $disableReason = 'هذا الطلب مسند بالفعل لهذا السائق';
                } elseif ($order->driver_id) {
                    $canAttach = false;
                    $disableReason = "هذا الطلب مسند لسائق آخر: {$order->driver->name}";
                }
            @endphp
            <div id="singleOrderCard" class="card border-0 shadow-sm mb-4">
                <div class="card-header {{ $canAttach ? 'bg-success' : 'bg-warning' }} text-white d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-box"></i> تفاصيل الطلب
                        @if(!$canAttach)
                            <small class="text-dark ms-2" style="font-size: 0.8em;">({{ $disableReason }})</small>
                        @else
                            <small class="text-white-50 ms-2" style="font-size: 0.7em;">(Ctrl+Enter = إسناد سريع)</small>
                        @endif
                    </span>
                    <span>
                        @if(!$canAttach)
                            <span class="badge bg-danger ms-2">
                                <i class="fas fa-ban"></i> لا يمكن الإسناد
                            </span>
                        @endif
                        <span class="badge bg-light text-dark">{{ $order->order_number }}</span>
                        @if($order->menafest)
                            <span class="badge bg-info ms-2">{{ $order->menafest->manafest_code }}</span>
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">المرسل</small>
                                <strong>{{ $order->sender }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">المرسل إليه</small>
                                <strong>{{ $order->recipient }}</strong>
                            </div>
                        </div>
                        <div class="col-md-1 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">نوع الدفع</small>
                                <strong>
                                    <span class="badge {{ $order->pay_type == 'تحصيل' ? 'bg-warning text-dark' : 'bg-success' }} px-3 py-2">
                                        {{ $order->pay_type }}
                                    </span>
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">العدد</small>
                                <strong>{{ $order->count }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">المبلغ</small>
                                <strong>{{ format_number($order->amount) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">ضد الشحن</small>
                                <strong>{{ format_number($order->anti_charger) }}</strong>
                            </div>
                        </div>
                         <div class="col-md-3 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">المحول</small>
                                <strong>{{ format_number($order->transmitted) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted d-block">السائق الحالي</small>
                                <strong id="currentDriverName" class="{{ $order->driver_id ? ($order->driver_id == $driver->id ? 'text-warning' : 'text-danger') : 'text-muted' }}">
                                    {{ $order->driver ? $order->driver->name : 'غير مسند' }}
                                </strong>
                            </div>
                        </div>
                        @if($order->is_paid)
                            <div class="col-12 mb-3">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-check-circle"></i> هذا الطلب مدفوع ولا يمكن إسناده
                                </div>
                            </div>
                        @endif
                        @if($order->notes)
                            <div class="col-12 mb-3">
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted d-block">ملاحظات</small>
                                    <strong>{{ $order->notes }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Attach button with AJAX --}}
                    <div class="mt-3">
                        <div class="d-flex gap-2 align-items-center">
                            @if($canAttach)
                                <button type="button" class="btn btn-success btn-lg px-5 attach-order-btn" 
                                    data-order-id="{{ $order->id }}"
                                    data-order-number="{{ $order->order_number }}"
                                    id="attachSingleBtn">
                                    <i class="fas fa-link me-2"></i>إسناد هذا الطلب للسائق
                                </button>
                                <a href="{{ route('orders.edit', ['order' => $order->id, 'return_url' => url()->full()]) }}" 
                                   class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-edit me-2"></i>تعديل الطلب
                                </a>
                                <small class="text-muted">أو اضغط <kbd>Ctrl</kbd> + <kbd>Enter</kbd></small>
                            @else
                                <button type="button" class="btn btn-secondary btn-lg px-5" disabled>
                                    <i class="fas fa-ban me-2"></i>{{ $disableReason }}
                                </button>
                                <a href="{{ route('orders.edit', ['order' => $order->id, 'return_url' => url()->full()]) }}" 
                                   class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-edit me-2"></i>تعديل الطلب
                                </a>
                                <small class="text-muted">اضغط <kbd>Esc</kbd> للبحث عن طلب آخر</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Multiple results - show table --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <div>
                        <span class="fw-bold">نتائج البحث: {{ $orders->total() }}</span>
                        @if(request('search'))
                            <span class="text-muted me-2">|</span>
                            <span class="text-primary">بحث: "{{ request('search') }}"</span>
                        @endif
                    </div>
                    <div>
                        <small class="text-muted">اضغط <kbd>Esc</kbd> لبحث جديد</small>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="ordersTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 60px;">إسناد</th>
                                    <th>#</th>
                                    <th>رقم الإيصال</th>
                                    <th>المنافست</th>
                                    <th>المرسل</th>
                                    <th>المرسل إليه</th>
                                    <th>نوع الدفع</th>
                                    <th>المبلغ</th>
                                    <th>ضد الشحن</th>
                                    <th>السائق الحالي</th>
                                    <th>حالة الدفع</th>
                                    <th>ملاحظات</th>
                                    <th>تعديل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    @php
                                        $canAttach = true;
                                        $disableTitle = '';
                                        
                                        if ($order->is_paid) {
                                            $canAttach = false;
                                            $disableTitle = 'الطلب مدفوع';
                                        } elseif ($order->driver_id && $order->driver_id == $driver->id) {
                                            $canAttach = false;
                                            $disableTitle = 'مسند بالفعل لهذا السائق';
                                        } elseif ($order->driver_id) {
                                            $canAttach = false;
                                            $disableTitle = 'مسند لسائق آخر';
                                        }
                                    @endphp
                                    <tr id="order-row-{{ $order->id }}" class="{{ !$canAttach ? 'table-secondary' : '' }}">
                                        <td>
                                            @if($canAttach)
                                                <button type="button" class="btn btn-success btn-sm rounded-pill attach-order-btn" 
                                                    data-order-id="{{ $order->id }}"
                                                    data-order-number="{{ $order->order_number }}"
                                                    title="إسناد سريع">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" disabled
                                                    title="{{ $disableTitle }}">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
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
                                                    <br>
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
                                        <td id="driver-cell-{{ $order->id }}">
                                            @if($order->driver)
                                                @if($order->driver_id == $driver->id)
                                                    <span class="badge bg-warning text-dark">{{ $order->driver->name }}</span>
                                                    <small class="text-warning d-block">هذا السائق</small>
                                                @else
                                                    <span class="badge bg-danger">{{ $order->driver->name }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->is_paid)
                                                <span class="badge bg-success">مدفوع</span>
                                                <small class="text-muted d-block">لا يمكن الإسناد</small>
                                            @else
                                                <span class="badge bg-warning text-dark">غير مدفوع</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 100px;"
                                                title="{{ $order->notes }}">
                                                {{ $order->notes ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('orders.edit', ['order' => $order->id, 'return_url' => url()->full()]) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($orders->hasPages())
                <div class="pagination-container">
                    <nav role="navigation" aria-label="Pagination Navigation">
                        <ul class="pagination">
                            @if($orders->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link prev-next"><i class="fas fa-chevron-right"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link prev-next" href="{{ $orders->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @endif

                            @if($orders->currentPage() > 3)
                                <li class="page-item"><a class="page-link" href="{{ $orders->url(1) }}">1</a></li>
                                @if($orders->currentPage() > 4)
                                    <li class="page-item disabled"><span class="page-link ellipsis">•••</span></li>
                                @endif
                            @endif

                            @foreach(range(1, $orders->lastPage()) as $i)
                                @if($i >= $orders->currentPage() - 2 && $i <= $orders->currentPage() + 2)
                                    @if($i == $orders->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link active-page">{{ $i }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $orders->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endif
                                @endif
                            @endforeach

                            @if($orders->currentPage() < $orders->lastPage() - 2)
                                @if($orders->currentPage() < $orders->lastPage() - 3)
                                    <li class="page-item disabled"><span class="page-link ellipsis">•••</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $orders->url($orders->lastPage()) }}">{{ $orders->lastPage() }}</a>
                                </li>
                            @endif

                            @if($orders->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link prev-next" href="{{ $orders->nextPageUrl() }}" rel="next">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link prev-next"><i class="fas fa-chevron-left"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
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

        kbd {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.85em;
        }

        .toast-container {
            z-index: 9999;
        }

        tr.row-attached {
            background-color: #f0fff4 !important;
            transition: background-color 0.5s ease;
        }
        
        .attach-order-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        :root {
            --pagination-radius: 14px;
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
            border-radius: var(--pagination-radius);
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            border: 2px solid #e2e8f0;
            cursor: pointer;
        }

        .page-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(99, 102, 241, 0.25);
        }

        .page-item.active .page-link {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(99, 102, 241, 0.3);
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
    </style>
@endpush
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toast notification helper
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toastNotification');
                const toastMessage = document.getElementById('toastMessage');
                
                // Remove existing classes
                toast.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                
                // Add appropriate class
                if (type === 'success') {
                    toast.classList.add('bg-success');
                } else if (type === 'error') {
                    toast.classList.add('bg-danger');
                } else if (type === 'warning') {
                    toast.classList.add('bg-warning');
                }
                
                toastMessage.textContent = message;
                
                const bsToast = new bootstrap.Toast(toast, {
                    delay: 3000,
                    autohide: true
                });
                bsToast.show();
            }

            // Reset the page like check_orders
            function resetPage() {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                
                // Hide single order card
                const singleOrderCard = document.getElementById('singleOrderCard');
                if (singleOrderCard) {
                    singleOrderCard.classList.add('d-none');
                }
                
                // Hide table and results bar
                const tableCard = document.querySelector('#ordersTable')?.closest('.card.border-0.shadow-sm');
                const resultsBar = document.querySelector('.card.border-0.shadow-sm.mb-3');
                
                if (tableCard) tableCard.classList.add('d-none');
                if (resultsBar) resultsBar.classList.add('d-none');
                
                // Hide pagination
                const paginationContainer = document.querySelector('.pagination-container');
                if (paginationContainer) paginationContainer.classList.add('d-none');
                
                // Show initial search prompt
                let initialCard = document.getElementById('initialSearchCard');
                if (initialCard) {
                    initialCard.classList.remove('d-none');
                }
                
                // Clear URL parameters without reload
                if (window.history && window.history.pushState) {
                    const newUrl = '{{ route("drivers.attach-orders", $driver) }}';
                    window.history.pushState({}, '', newUrl);
                }
            }

            // Attach order via AJAX
            function attachOrder(orderId, orderNumber, buttonElement) {
                // Disable button and show loading
                const originalHtml = buttonElement.innerHTML;
                buttonElement.disabled = true;
                buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                fetch('{{ route("drivers.attach-orders.store", $driver) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        order_id: orderId 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        showToast(data.message, 'success');
                        
                        // Update the UI
                        updateOrderRowAfterAttach(orderId, buttonElement);
                        
                    } else {
                        showToast(data.message || 'حدث خطأ أثناء الإسناد', 'error');
                        buttonElement.disabled = false;
                        buttonElement.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('حدث خطأ في الاتصال', 'error');
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = originalHtml;
                });
            }
            
            function updateOrderRowAfterAttach(orderId, buttonElement) {
                // Update the row to show attached status
                const row = document.getElementById(`order-row-${orderId}`);
                if (row) {
                    // Add a highlight class
                    row.classList.add('row-attached');
                    row.classList.add('table-success');
                    
                    // Change button to show attached state
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-success');
                    buttonElement.innerHTML = '<i class="fas fa-check"></i> تم';
                    buttonElement.disabled = true;
                    buttonElement.title = 'تم الإسناد';
                    
                    // Update driver cell
                    const driverCell = document.getElementById(`driver-cell-${orderId}`);
                    if (driverCell) {
                        driverCell.innerHTML = '<span class="badge bg-warning text-dark">{{ $driver->name }}</span><small class="text-warning d-block">هذا السائق</small>';
                    }
                    
                    // Fade out after 1 second
                    setTimeout(() => {
                        row.style.transition = 'all 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        
                        setTimeout(() => {
                            row.remove();
                            
                            // Check if this was the last row in the table
                            const tbody = document.querySelector('#ordersTable tbody');
                            if (tbody && tbody.children.length === 0) {
                                // All orders have been attached, reset the page
                                setTimeout(() => {
                                    resetPage();
                                }, 300);
                            }
                        }, 500);
                    }, 1000);
                }
                
                // If single order view, update the button then reset
                if (buttonElement.id === 'attachSingleBtn') {
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-success');
                    buttonElement.innerHTML = '<i class="fas fa-check me-2"></i>تم الإسناد بنجاح';
                    buttonElement.disabled = true;
                    
                    // Update driver name
                    const currentDriverName = document.getElementById('currentDriverName');
                    if (currentDriverName) {
                        currentDriverName.textContent = '{{ $driver->name }}';
                        currentDriverName.className = 'text-warning fw-bold';
                    }
                    
                    // Reset the page after 1.5 seconds - just like check_orders
                    setTimeout(() => {
                        resetPage();
                    }, 1500);
                }
            }

            // Attach event listeners to all attach buttons
            document.querySelectorAll('.attach-order-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    const orderNumber = this.dataset.orderNumber;
                    
                    // Show confirmation for single view
                    if (this.id === 'attachSingleBtn') {
                        if (confirm(`هل أنت متأكد من إسناد الطلب #${orderNumber} للسائق {{ $driver->name }}؟`)) {
                            attachOrder(orderId, orderNumber, this);
                        }
                    } else {
                        // For table buttons, attach directly
                        attachOrder(orderId, orderNumber, this);
                    }
                });
            });

            // ─── Keyboard Shortcuts ───
            document.addEventListener('keydown', function(e) {
                // Ctrl+Enter to attach single order (only if attachable)
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    const singleBtn = document.getElementById('attachSingleBtn');
                    if (singleBtn && !singleBtn.disabled) {
                        e.preventDefault();
                        const orderId = singleBtn.dataset.orderId;
                        const orderNumber = singleBtn.dataset.orderNumber;
                        if (confirm(`هل أنت متأكد من إسناد الطلب #${orderNumber} للسائق {{ $driver->name }}؟`)) {
                            attachOrder(orderId, orderNumber, singleBtn);
                        }
                    }
                }

                // Escape key - clear search and focus on search input for new search
                if (e.key === 'Escape') {
                    const searchInput = document.getElementById('searchInput');
                    if (searchInput) {
                        e.preventDefault();
                        // Clear the search input
                        searchInput.value = '';
                        // Focus on it
                        searchInput.focus();
                    }
                }
            });
        });
    </script>
@endpush