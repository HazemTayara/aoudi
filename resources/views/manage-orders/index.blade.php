@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-primary-light me-3">
                    <i class="fas fa-boxes text-primary fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">إدارة جميع الطلبات</h2>
                    <p class="text-muted mb-0">عرض وإدارة جميع الطلبات الواردة</p>
                </div>
            </div>

            <div>
                @php 
                    $trashedCount = \App\Models\Order::onlyTrashed()->count();
                @endphp
                <a href="{{ route('orders.trashed') }}" class="btn btn-warning me-2">
                    <i class="fas fa-trash-alt"></i> سلة المحذوفات
                    @if($trashedCount > 0)
                        <span class="badge bg-danger ms-1">{{ $trashedCount }}</span>
                    @endif
                </a>
            </div>
        </div>
        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-box text-primary"></i>
                        <span class="stat-label-sm">إجمالي الطلبات</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_count']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-cubes text-primary"></i>
                        <span class="stat-label-sm">إجمالي العدد</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_items']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm border-warning">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-money-bill-wave text-warning"></i>
                        <span class="stat-label-sm">تحصيل ({{ $stats['cash_count'] }})</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_cash_amount']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm border-success">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-credit-card text-success"></i>
                        <span class="stat-label-sm">مسبق ({{ $stats['prepaid_count'] }})</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_prepaid_amount']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-coins text-primary"></i>
                        <span class="stat-label-sm">إجمالي المبلغ</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_amount']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-shield-alt text-primary"></i>
                        <span class="stat-label-sm">ضد الشحن ({{ $stats['anti_charger_count'] }})</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_anti_charger']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-exchange-alt text-primary"></i>
                        <span class="stat-label-sm">المحول ({{ $stats['transmitted_count'] }})</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_transmitted']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-random text-primary"></i>
                        <span class="stat-label-sm">متفرقات ({{ $stats['miscellaneous_count'] }})</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_miscellaneous']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-percent text-danger"></i>
                        <span class="stat-label-sm">الخصم ({{ $stats['discount_count'] }})</span>
                    </div>
                    <h4 class="stat-value-sm">{{ format_number($stats['total_discount']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm border-success">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="stat-label-sm">مدفوع</span>
                    </div>
                    <h4 class="stat-value-sm text-success">{{ format_number($stats['paid_count']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm border-danger">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-times-circle text-danger"></i>
                        <span class="stat-label-sm">غير مدفوع</span>
                    </div>
                    <h4 class="stat-value-sm text-danger">{{ format_number($stats['unpaid_count']) }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-sm border-info">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-archive text-info"></i>
                        <span class="stat-label-sm">موجود</span>
                    </div>
                    <h4 class="stat-value-sm text-info">{{ format_number($stats['exist_count']) }}</h4>
                </div>
            </div>
        </div>
        <!-- Server-side Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-filter text-primary ms-2"></i>بحث وفلترة</h5>
                <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse">
                    <i class="fas fa-chevron-down me-1"></i>عرض/إخفاء
                </button>
            </div>
            <div class="collapse show" id="filterCollapse">
                <div class="card-body pt-0">
                    <form method="GET" action="{{ route('manage-orders.index') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Text search fields -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">رقم الإيصال</label>
                                <input type="text" name="order_number" class="form-control form-control-sm"
                                    value="{{ request('order_number') }}" placeholder="بحث...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المحتوى</label>
                                <input type="text" name="content" class="form-control form-control-sm"
                                    value="{{ request('content') }}" placeholder="بحث...">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-bold small">العدد</label>
                                <input type="number" name="count" class="form-control form-control-sm"
                                    value="{{ request('count') }}" placeholder="...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المرسل</label>
                                <input type="text" name="sender" class="form-control form-control-sm"
                                    value="{{ request('sender') }}" placeholder="بحث...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المرسل إليه</label>
                                <input type="text" name="recipient" class="form-control form-control-sm"
                                    value="{{ request('recipient') }}" placeholder="بحث...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">كود المنافست</label>
                                <input type="text" name="menafest_code" class="form-control form-control-sm"
                                    value="{{ request('menafest_code') }}" placeholder="بحث...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">اسم السائق</label>
                                <input type="text" name="driver_name" class="form-control form-control-sm"
                                    value="{{ request('driver_name') }}" placeholder="بحث...">
                            </div>
                            <!-- Dropdowns -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">نوع الدفع</label>
                                <select name="pay_type" class="form-control form-control-sm">
                                    <option value="">الكل</option>
                                    <option value="تحصيل" {{ request('pay_type') == 'تحصيل' ? 'selected' : '' }}>تحصيل
                                    </option>
                                    <option value="مسبق" {{ request('pay_type') == 'مسبق' ? 'selected' : '' }}>مسبق</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">تم الاستلام</label>
                                <select name="is_paid" class="form-control form-control-sm">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>نعم</option>
                                    <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>لا</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">موجود</label>
                                <select name="is_exist" class="form-control form-control-sm">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_exist') === '1' ? 'selected' : '' }}>نعم</option>
                                    <option value="0" {{ request('is_exist') === '0' ? 'selected' : '' }}>لا</option>
                                </select>
                            </div>
                            <!-- Numeric range filters -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المبلغ من</label>
                                <input type="number" step="0.01" name="amount_min" class="form-control form-control-sm"
                                    value="{{ request('amount_min') }}" placeholder="من">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المبلغ إلى</label>
                                <input type="number" step="0.01" name="amount_max" class="form-control form-control-sm"
                                    value="{{ request('amount_max') }}" placeholder="إلى">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ضد الشحن من</label>
                                <input type="number" step="0.01" name="anti_charger_min"
                                    class="form-control form-control-sm" value="{{ request('anti_charger_min') }}"
                                    placeholder="من">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ضد الشحن إلى</label>
                                <input type="number" step="0.01" name="anti_charger_max"
                                    class="form-control form-control-sm" value="{{ request('anti_charger_max') }}"
                                    placeholder="إلى">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المحول من</label>
                                <input type="number" step="0.01" name="transmitted_min" class="form-control form-control-sm"
                                    value="{{ request('transmitted_min') }}" placeholder="من">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المحول إلى</label>
                                <input type="number" step="0.01" name="transmitted_max" class="form-control form-control-sm"
                                    value="{{ request('transmitted_max') }}" placeholder="إلى">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">متفرقات من</label>
                                <input type="number" step="0.01" name="miscellaneous_min"
                                    class="form-control form-control-sm" value="{{ request('miscellaneous_min') }}"
                                    placeholder="من">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">متفرقات إلى</label>
                                <input type="number" step="0.01" name="miscellaneous_max"
                                    class="form-control form-control-sm" value="{{ request('miscellaneous_max') }}"
                                    placeholder="إلى">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">الخصم من</label>
                                <input type="number" step="0.01" name="discount_min" class="form-control form-control-sm"
                                    value="{{ request('discount_min') }}" placeholder="من">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">الخصم إلى</label>
                                <input type="number" step="0.01" name="discount_max" class="form-control form-control-sm"
                                    value="{{ request('discount_max') }}" placeholder="إلى">
                            </div>
                            <!-- Date range filters -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">تاريخ الدفع من</label>
                                <input type="date" name="paid_from" class="form-control form-control-sm"
                                    value="{{ request('paid_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">تاريخ الدفع إلى</label>
                                <input type="date" name="paid_to" class="form-control form-control-sm"
                                    value="{{ request('paid_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">تاريخ الإنشاء من</label>
                                <input type="date" name="created_from" class="form-control form-control-sm"
                                    value="{{ request('created_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">تاريخ الإنشاء إلى</label>
                                <input type="date" name="created_to" class="form-control form-control-sm"
                                    value="{{ request('created_to') }}">
                            </div>
                            <!-- Notes search -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">ملاحظات</label>
                                <input type="text" name="notes" class="form-control form-control-sm"
                                    value="{{ request('notes') }}" placeholder="بحث في الملاحظات...">
                            </div>
                            <!-- Action buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('manage-orders.index') }}" class="btn btn-outline-secondary px-4">
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
        </div>
        <!-- Orders Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table text-primary ms-2"></i>
                    الطلبات
                    <small class="text-muted">({{ format_number($orders->total()) }} نتيجة)</small>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="ordersTable">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>رقم الإيصال</th>
                                <th>المنافست</th>
                                <th>المحتوى</th>
                                <th>العدد</th>
                                <th>المرسل</th>
                                <th>المرسل إليه</th>
                                <th>نوع الدفع</th>
                                <th>المبلغ</th>
                                <th>ضد الشحن</th>
                                <th>المحول</th>
                                <th>متفرقات</th>
                                <th>الخصم</th>
                                <th>تم الاستلام</th>
                                <th>موجود</th>
                                <th>السائق</th>
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
                                            <small>
                                                {{ $order->menafest->manafest_code }}
                                                {{ '|' }}
                                                <span class="text-muted">{{ $order->menafest->fromCity->name }}</span>
                                            </small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $order->content }}</td>
                                    <td>{{ format_number($order->count) }}</td>
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
                                    <td>{{ format_number($order->transmitted) }}</td>
                                    <td>{{ format_number($order->miscellaneous) }}</td>
                                    <td>{{ format_number($order->discount) }}</td>
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
                                        @if($order->driver)
                                            <small class="text-muted">{{ $order->driver->name }}</small>
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
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
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('orders.edit', $order) }}"
                                                class="btn btn-sm btn-outline-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-btn" 
                                                data-id="{{ $order->id }}"
                                                data-number="{{ $order->order_number }}"
                                                title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="18" class="text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">لا توجد طلبات</h5>
                                        <p class="text-muted">لم يتم العثور على طلبات مطابقة لمعايير البحث</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Enhanced Pagination Component -->
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
            transition: 0.2s;
        }

        .stat-card-sm:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        .stat-card-sm.border-warning {
            border-left: 3px solid #ffc107 !important;
        }

        .stat-card-sm.border-success {
            border-left: 3px solid #28a745 !important;
        }

        .stat-card-sm.border-danger {
            border-left: 3px solid #dc3545 !important;
        }

        .stat-card-sm.border-info {
            border-left: 3px solid #17a2b8 !important;
        }

        .stat-label-sm {
            font-size: 0.78rem;
            color: #6c757d;
        }

        .stat-value-sm {
            font-size: 1.3rem;
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
            font-size: 0.82rem;
            border-bottom: 2px solid var(--primary-light);
            padding: 0.85rem 0.6rem;
            white-space: nowrap;
        }

        .table td {
            padding: 0.7rem 0.6rem;
            color: #2c3e50;
            vertical-align: middle;
            font-size: 0.88rem;
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
            font-size: 0.82rem;
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

        .form-control-sm {
            border-radius: 10px;
            font-size: 0.85rem;
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

            // Delete button handler with SweetAlert
            $(document).on('click', '.delete-btn', function() {
                const orderId = $(this).data('id');
                const orderNumber = $(this).data('number');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    html: `هل تريد حذف الطلب رقم <strong>${orderNumber}</strong>؟`,
                    text: "سيتم نقل الطلب إلى سلة المحذوفات",
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
                            url: `/orders/${orderId}`,
                            type: 'DELETE',
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
                                let errorMessage = 'حدث خطأ أثناء حذف الطلب';
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
            $(document).on('input', '.notes-input', function () {
                let input = $(this);
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
            $(document).on('keypress', '.notes-input', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).siblings('.btn-save-notes').click();
                }
            });
        });
    </script>
@endpush