@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4"dir="rtl">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-primary-light me-3">
                    <i class="fas fa-link text-primary fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">
                        إسناد طلبات للسائق:
                        <span class="text-primary fw-bolder mx-1">{{ $driver->name }}</span>
                    </h2>
                    <p class="text-muted mb-0">ابحث عن الطلبات لإسنادها لهذا السائق</p>
                </div>
            </div>
            <a href="{{ route('drivers.orders', $driver) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                <i class="fas fa-arrow-right me-2"></i>العودة لطلبات السائق
            </a>
        </div>

        {{-- Toast Container --}}
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999;">
            <div id="toastNotification" class="toast align-items-center text-white border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body" id="toastMessage"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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

        <!-- Search Card -->
        <div class="card border-0 shadow-lg rounded-4 mb-4">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-search text-primary ms-2"></i>بحث عن طلبات للإسناد</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.attach-orders', $driver) }}" id="searchForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-10">
                            <label class="form-label fw-bold small">بحث</label>
                            <input type="text" name="search" class="form-control form-control-lg search-input"
                                value="{{ request('search') }}" 
                                placeholder="أدخل رقم الإيصال أو اسم المرسل إليه..." 
                                autofocus id="searchInput"
                                dir="rtl">
                            <small class="text-muted">يمكنك البحث برقم الإيصال أو اسم المرسل إليه | اضغط <kbd>Esc</kbd> لمسح البحث والبدء من جديد</small>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <a href="{{ route('drivers.attach-orders', $driver) }}" class="btn btn-outline-secondary flex-grow-1 rounded-3" title="مسح البحث">
                                <i class="fas fa-redo"></i>
                            </a>
                            <button type="submit" class="btn btn-primary flex-grow-1 rounded-3">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(!$hasSearch)
            {{-- No search performed yet --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">ابحث عن الطلبات للإسناد</h5>
                    <p class="text-muted">أدخل رقم الإيصال أو اسم المرسل إليه في حقل البحث أعلاه</p>
                </div>
            </div>
        @elseif($orders->isEmpty())
            {{-- No results found --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد طلبات تطابق بحثك</h5>
                    <p class="text-muted">حاول تعديل معايير البحث أو استخدم كلمة بحث مختلفة</p>
                </div>
            </div>
        @elseif($orders->count() === 1 && $orders->total() === 1)
            {{-- Single result - تم تصميم البطاقة المحسنة --}}
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

            <div class="card border-0 shadow-lg rounded-4 mb-4" id="singleOrderCard">
                <div class="card-header {{ $canAttach ? 'bg-success' : 'bg-warning' }} text-white d-flex justify-content-between align-items-center rounded-top-4">
                    <span>
                        تفاصيل الطلب
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
                    </span>
                </div>
                <div class="card-body">
                    {{-- الصف العلوي: رقم الإيصال، كود المنافست والمدينة --}}
                    <div class="d-flex align-items-center mb-4">
                        <h3 class="mb-0 me-3 fw-bold">#{{ $order->order_number }}</h3>
                        @if($order->menafest)
                            <span class="badge bg-info fs-6 me-2">{{ $order->menafest->manafest_code }}</span>
                            <span class="text-muted fs-6">{{ $order->menafest->fromCity->name }}</span>
                        @endif
                    </div>

                    {{-- المرسل والمرسل إليه في المنتصف العلوي --}}
                    <div class="row mb-4">
                        <div class="col-6 text-center">
                            <div class="bg-light p-3 rounded-3">
                                <small class="text-muted d-block mb-1">المرسل</small>
                                <strong class="fs-5">{{ $order->sender }}</strong>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="bg-light p-3 rounded-3">
                                <small class="text-muted d-block mb-1">المرسل إليه</small>
                                <strong class="fs-5">{{ $order->recipient }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- المحتوى والعدد (نفس الأهمية) --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-3">
                                <small class="text-muted d-block">المحتوى</small>
                                <strong class="fs-5">{{ $order->content ?? '—' }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-3">
                                <small class="text-muted d-block">العدد</small>
                                <strong class="fs-5">{{ format_number($order->count) }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- المبلغ ونوع الدفع في صف واحد --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-3">
                                <small class="text-muted d-block">المبلغ</small>
                                <strong class="fs-5 {{ $order->amount > 0 ? 'text-success' : '' }}">
                                    {{ format_number($order->amount) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-3">
                                <small class="text-muted d-block">نوع الدفع</small>
                                <strong>
                                    <span class="badge {{ $order->pay_type == 'تحصيل' ? 'bg-warning text-dark' : 'bg-success' }} px-3 py-2">
                                        {{ $order->pay_type }}
                                    </span>
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- ضد الشحن، المحول، المتنوعات، الخصم (كلها بنفس الأهمية) --}}
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="bg-light p-2 rounded-3 text-center">
                                <small class="text-muted d-block">ضد الشحن</small>
                                <strong class="{{ $order->anti_charger > 0 ? 'text-success' : '' }}">
                                    {{ format_number($order->anti_charger) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light p-2 rounded-3 text-center">
                                <small class="text-muted d-block">المُحوّل</small>
                                <strong class="{{ $order->transmitted > 0 ? 'text-success' : '' }}">
                                    {{ format_number($order->transmitted) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light p-2 rounded-3 text-center">
                                <small class="text-muted d-block">متنوعات</small>
                                <strong class="{{ $order->miscellaneous > 0 ? 'text-success' : '' }}">
                                    {{ format_number($order->miscellaneous) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light p-2 rounded-3 text-center">
                                <small class="text-muted d-block">الخصم</small>
                                <strong class="{{ $order->discount > 0 ? 'text-success' : '' }}">
                                    {{ format_number($order->discount) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- السائق الحالي وتاريخ الإسناد، مع حالة الدفع --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">السائق الحالي:</small>
                            @if($order->driver)
                                <strong class="{{ $order->driver_id == $driver->id ? 'text-warning' : 'text-danger' }}">
                                    {{ $order->driver->name }}
                                </strong>
                                @if($order->assigned_at)
                                    <small class="text-muted d-block">{{ arabic_date_time($order->assigned_at) }}</small>
                                @endif
                            @else
                                <span class="text-muted">غير مسند</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <small class="text-muted">حالة الدفع:</small>
                            @if($order->is_paid)
                                <span class="badge bg-success">مدفوع</span>
                                @if($order->paid_at)
                                    <small class="text-muted d-block">{{ arabic_date_time($order->paid_at) }}</small>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">غير مدفوع</span>
                            @endif
                        </div>
                    </div>

                    {{-- تاريخ الإنشاء --}}
                    <div class="mb-3">
                        <small class="text-muted">تاريخ الإنشاء:</small>
                        <strong>{{ arabic_date($order->created_at) }}</strong>
                    </div>

                    {{-- ملاحظات (غير مهمة جداً) --}}
                    @if($order->notes)
                        <div class="mb-3">
                            <small class="text-muted">ملاحظات:</small>
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif

                    {{-- أزرار الإسناد --}}
                    <div class="mt-3">
                        <div class="d-flex gap-2 align-items-center">
                            @if($canAttach)
                                <button type="button" class="btn btn-success btn-lg px-5 attach-order-btn rounded-3" 
                                    data-order-id="{{ $order->id }}"
                                    data-order-number="{{ $order->order_number }}"
                                    id="attachSingleBtn">
                                    <i class="fas fa-link me-2"></i>إسناد هذا الطلب للسائق
                                </button>
                                <a href="{{ route('orders.edit', ['order' => $order->id, 'return_url' => url()->full()]) }}" 
                                   class="btn btn-outline-primary btn-lg rounded-3">
                                    <i class="fas fa-edit me-2"></i>تعديل الطلب
                                </a>
                                <small class="text-muted">أو اضغط <kbd>Ctrl</kbd> + <kbd>Enter</kbd></small>
                            @else
                                <button type="button" class="btn btn-secondary btn-lg px-5 rounded-3" disabled>
                                    <i class="fas fa-ban me-2"></i>{{ $disableReason }}
                                </button>
                                <a href="{{ route('orders.edit', ['order' => $order->id, 'return_url' => url()->full()]) }}" 
                                   class="btn btn-outline-primary btn-lg rounded-3">
                                    <i class="fas fa-edit me-2"></i>تعديل الطلب
                                </a>
                                <small class="text-muted">اضغط <kbd>Esc</kbd> للبحث عن طلب آخر</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Multiple results - enhanced table --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3">
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

            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="ordersTable">
                            <thead class="bg-light text-nowrap">
                                <tr>
                                    <th style="width: 60px;">إسناد</th>
                                    <th>#</th>
                                    <th>رقم الإيصال</th>
                                    <th>المنافست</th>
                                    <th>المرسل</th>
                                    <th>المرسل إليه</th>
                                    <th>المحتوى</th>
                                    <th>العدد</th>
                                    <th>المبلغ</th>
                                    <th>نوع الدفع</th>
                                    <th>ضد الشحن</th>
                                    <th>المُحوّل</th>
                                    <th>متنوعات</th>
                                    <th>الخصم</th>
                                    <th>تاريخ الإنشاء</th>
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
                                                <div class="d-flex flex-column">
                                                    <small class="fw-bold">{{ $order->menafest->manafest_code }}</small>
                                                    <small class="text-muted">{{ $order->menafest->fromCity->name }}</small>
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $order->sender }}</td>
                                        <td>{{ $order->recipient }}</td>
                                        <td>{{ $order->content ?? '—' }}</td>
                                        <td>{{ format_number($order->count) }}</td>
                                        <td class="{{ $order->amount > 0 ? 'text-success fw-bold' : '' }}">{{ format_number($order->amount) }}</td>
                                        <td>
                                            @if($order->pay_type == 'تحصيل')
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">تحصيل</span>
                                            @else
                                                <span class="badge bg-success text-white px-3 py-2 rounded-pill">مسبق</span>
                                            @endif
                                        </td>
                                        <td class="{{ $order->anti_charger > 0 ? 'text-success fw-bold' : '' }}">{{ format_number($order->anti_charger) }}</td>
                                        <td class="{{ $order->transmitted > 0 ? 'text-success fw-bold' : '' }}">{{ format_number($order->transmitted) }}</td>
                                        <td class="{{ $order->miscellaneous > 0 ? 'text-success fw-bold' : '' }}">{{ format_number($order->miscellaneous) }}</td>
                                        <td class="{{ $order->discount > 0 ? 'text-success fw-bold' : '' }}">{{ format_number($order->discount) }}</td>
                                        <td>{{ arabic_date($order->created_at) }}</td>
                                        <td id="driver-cell-{{ $order->id }}">
                                            @if($order->driver)
                                                <strong class="{{ $order->driver_id == $driver->id ? 'text-warning' : 'text-danger' }}">
                                                    {{ $order->driver->name }}
                                                </strong>
                                                @if($order->assigned_at)
                                                    <small class="text-muted d-block">{{ arabic_date_time($order->assigned_at) }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->is_paid)
                                                <span class="badge bg-success">مدفوع</span>
                                                @if($order->paid_at)
                                                    <small class="text-muted d-block">{{ arabic_date_time($order->paid_at) }}</small>
                                                @endif
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
                                               class="btn btn-sm btn-outline-primary rounded-pill">
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
        @endif
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-light: #eef2ff;
            --accent-color: #4f46e5;
            --pagination-radius: 14px;
        }

        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
        }

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

        /* حقل البحث */
        .search-input {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        kbd {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.85em;
        }

        /* جداول */
        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 700;
            font-size: 0.85rem;
            border-bottom: 2px solid var(--accent-color);
            padding: 1rem 0.5rem;
            white-space: nowrap;
        }

        .table td {
            padding: 0.75rem 0.5rem;
            color: #2c3e50;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-hover tbody tr:hover {
            background-color: #f0f4ff;
        }

        tr.row-attached {
            background-color: #f0fff4 !important;
            transition: background-color 0.5s ease;
        }

        .attach-order-btn {
            transition: all 0.2s ease;
        }

        .attach-order-btn:active {
            transform: scale(0.95);
        }

        .attach-order-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');

            // ضمان معالجة backspace بشكل صحيح في الحقل العربي
            if (searchInput) {
                // لا حاجة لكتابة كود إضافي فالحقل dir="rtl" يحل المشكلة
                searchInput.focus();
            }

            // Toast helper
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toastNotification');
                const toastMessage = document.getElementById('toastMessage');

                toast.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                if (type === 'success') toast.classList.add('bg-success');
                else if (type === 'error') toast.classList.add('bg-danger');
                else if (type === 'warning') toast.classList.add('bg-warning');

                toastMessage.textContent = message;
                const bsToast = new bootstrap.Toast(toast, { delay: 3000, autohide: true });
                bsToast.show();
            }

            // إعادة ضبط الصفحة لاستقبال بحث جديد
            function resetPage() {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }

                // إخفاء البطاقة المفردة
                const singleCard = document.getElementById('singleOrderCard');
                if (singleCard) singleCard.classList.add('d-none');

                // إخفاء الجدول وشريط النتائج
                const tableCard = document.querySelector('#ordersTable')?.closest('.card');
                const resultsBar = document.querySelector('.card.border-0.shadow-sm.mb-3');
                if (tableCard) tableCard.classList.add('d-none');
                if (resultsBar) resultsBar.classList.add('d-none');

                // إخفاء الترقيم
                document.querySelectorAll('.pagination-container').forEach(el => el.classList.add('d-none'));

                // عرض بطاقة البحث الفارغة إن وجدت
                const initialCard = document.getElementById('initialSearchCard');
                if (initialCard) initialCard.classList.remove('d-none');

                // تنظيف الـ URL
                if (window.history && window.history.pushState) {
                    window.history.pushState({}, '', '{{ route("drivers.attach-orders", $driver) }}');
                }
            }

            // إسناد الطلب عبر AJAX
            function attachOrder(orderId, orderNumber, buttonElement) {
                const originalHtml = buttonElement.innerHTML;
                buttonElement.disabled = true;
                buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch('{{ route("drivers.attach-orders.store", $driver) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        updateOrderRowAfterAttach(orderId, buttonElement);
                    } else {
                        showToast(data.message || 'حدث خطأ أثناء الإسناد', 'error');
                        buttonElement.disabled = false;
                        buttonElement.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    showToast('حدث خطأ في الاتصال', 'error');
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = originalHtml;
                });
            }

            function updateOrderRowAfterAttach(orderId, buttonElement) {
                const row = document.getElementById(`order-row-${orderId}`);
                if (row) {
                    row.classList.add('row-attached', 'table-success');
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-success');
                    buttonElement.innerHTML = '<i class="fas fa-check"></i> تم';
                    buttonElement.disabled = true;
                    buttonElement.title = 'تم الإسناد';

                    const driverCell = document.getElementById(`driver-cell-${orderId}`);
                    if (driverCell) {
                        driverCell.innerHTML = '<strong class="text-warning">{{ $driver->name }}</strong><small class="text-muted d-block">هذا السائق</small>';
                    }

                    setTimeout(() => {
                        row.style.transition = 'all 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            row.remove();
                            if (document.querySelectorAll('#ordersTable tbody tr').length === 0) {
                                setTimeout(resetPage, 300);
                            }
                        }, 500);
                    }, 1000);
                }

                // التعامل مع زر البطاقة المفردة
                if (buttonElement.id === 'attachSingleBtn') {
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-success');
                    buttonElement.innerHTML = '<i class="fas fa-check me-2"></i>تم الإسناد بنجاح';
                    buttonElement.disabled = true;

                    const currentDriver = document.getElementById('currentDriverName');
                    if (currentDriver) {
                        currentDriver.textContent = '{{ $driver->name }}';
                        currentDriver.className = 'text-warning fw-bold';
                    }

                    setTimeout(resetPage, 1500);
                }
            }

            // ربط أحداث النقر على أزرار الإسناد
            document.querySelectorAll('.attach-order-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    const orderNumber = this.dataset.orderNumber;
                    if (this.id === 'attachSingleBtn') {
                        if (confirm(`هل أنت متأكد من إسناد الطلب #${orderNumber} للسائق {{ $driver->name }}؟`)) {
                            attachOrder(orderId, orderNumber, this);
                        }
                    } else {
                        attachOrder(orderId, orderNumber, this);
                    }
                });
            });

            // اختصارات لوحة المفاتيح
            document.addEventListener('keydown', function(e) {
                // Ctrl+Enter للإسناد السريع في البطاقة المفردة
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

                // Escape لمسح الحقل والبدء من جديد
                if (e.key === 'Escape') {
                    // لا نمنع السلوك الافتراضي إذا كان المستخدم في حقل إدخال آخر
                    if (document.activeElement && document.activeElement.tagName === 'INPUT' && document.activeElement !== searchInput) {
                        return; // اترك السلوك الافتراضي لحقول أخرى
                    }
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                    }
                    // إخفاء النتائج إذا كانت موجودة (اختياري)
                    const singleCard = document.getElementById('singleOrderCard');
                    if (singleCard) singleCard.classList.add('d-none');
                    const tableCard = document.querySelector('#ordersTable')?.closest('.card');
                    if (tableCard) tableCard.classList.add('d-none');
                    // إظهار بطاقة البحث الأولية إن وجدت
                    const initialCard = document.getElementById('initialSearchCard');
                    if (initialCard) initialCard.classList.remove('d-none');
                }
            });

            // التأكد من إمكانية استخدام Backspace بشكل طبيعي - لا حاجة لأي كود إضافي
        });
    </script>
@endpush