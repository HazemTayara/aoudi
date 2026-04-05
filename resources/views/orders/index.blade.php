@extends('layouts.app')

@section('content')
    @php
        $type = $menafest->menafestType();
    @endphp
    <div class="container-fluidpy-4">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-primary-light me-3">
                    <i class="fas fa-box text-primary fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1">إدارة الطلبات</h2>
                    <p class="text-muted">منافست: {{ $menafest->manafest_code }} |
                        @if ($type == 'incoming')
                            {{ $menafest->fromCity->name }}
                        @else
                            {{ $menafest->toCity->name }}
                        @endif
                    </p>

                </div>
            </div>
            @if ($type == 'incoming')
                <a href="{{ route('menafests.orders.upload', $menafest) }}"
                    class="btn btn-outline-success btn-lg rounded-pill px-4">
                    <i class="fas fa-file-excel me-2"></i>رفع Excel
                </a>
            @else
                <a href="{{ route('menafests.export-outgoing', $menafest) }}"
                    class="btn btn-outline-success btn-lg rounded-pill px-4">
                    <i class="fas fa-file-excel me-2"></i>تصدير Excel
                </a>
            @endif

            @php 
                $trashedCount = $menafest->orders()->onlyTrashed()->count();
            @endphp

            <a href="{{ route('menafests.orders.trashed', $menafest) }}"
                class="btn btn-warning btn-lg rounded-pill px-4 me-2">
                <i class="fas fa-trash-alt me-2"></i>سلة المحذوفات
                @if($trashedCount > 0)
                    <span class="badge bg-danger ms-1">{{ $trashedCount }}</span>
                @endif
            </a>
        </div>

        <!-- Quick Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-light">
                            <i class="fas fa-box text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <span class="stat-label">إجمالي الطلبات</span>
                            <h3 class="stat-value" id="total-orders">{{ format_number($orders->count()) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-light">
                            <i class="fas fa-money-bill-wave text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <span class="stat-label">تحصيل (عدد)</span>
                            <h3 class="stat-value" id="cash-count">
                                {{ format_number($orders->where('pay_type', 'تحصيل')->count()) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-light">
                            <i class="fas fa-credit-card text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <span class="stat-label">مسبق (عدد)</span>
                            <h3 class="stat-value" id="prepaid-count">
                                {{ format_number($orders->where('pay_type', 'مسبق')->count()) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-light">
                            <i class="fas fa-cubes text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <span class="stat-label">إجمالي العدد</span>
                            <h3 class="stat-value" id="total-count">{{ format_number($orders->sum('count')) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-light">
                            <i class="fas fa-coins text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <span class="stat-label">إجمالي تحصيل</span>
                            <h3 class="stat-value" id="total-cash-amount">
                                {{ format_number($orders->where('pay_type', 'تحصيل')->sum('amount')) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-light">
                            <i class="fas fa-coins text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <span class="stat-label">إجمالي مسبق</span>
                            <h3 class="stat-value" id="total-prepaid-amount">
                                {{ format_number($orders->where('pay_type', 'مسبق')->sum('amount')) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Add Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-plus-circle text-primary ms-2"></i>إضافة طلب جديد</h5>
            </div>
            <div class="card-body">
                <form id="quickAddForm" method="POST"
                    action="{{ route('menafests.orders.store', ['menafest' => $menafest, 'type' => $type]) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">رقم الإيصال *</label>
                            <input type="text" name="order_number" id="order_number" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">المحتوى</label>
                            <input type="text" name="content" id="content" class="form-control" value="طرد">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">العدد *</label>
                            <input type="number" name="count" id="count" class="form-control" required min="1" value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">المرسل *</label>
                            <input type="text" name="sender" id="sender" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">المرسل إليه *</label>
                            <input type="text" name="recipient" id="recipient" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">نوع الدفع *</label>
                            <select name="pay_type" id="pay_type" class="form-control" required>
                                <option value="تحصيل">تحصيل</option>
                                <option value="مسبق">مسبق</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">المبلغ *</label>
                            <input type="text" name="amount" id="amount" class="form-control price-input" required
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">ضد الشحن</label>
                            <input type="text" name="anti_charger" id="anti_charger" class="form-control price-input"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">المحول</label>
                            <input type="text" name="transmitted" id="transmitted" class="form-control price-input"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">متفرقات</label>
                            <input type="text" name="miscellaneous" id="miscellaneous" class="form-control price-input"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">الخصم</label>
                            <input type="text" name="discount" id="discount" class="form-control price-input"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ملاحظات</label>
                            <input type="text" name="notes" id="notes" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" id="addOrderBtn" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-plus"></i> إضافة
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Loading Indicator (Hidden by default) -->
                <div id="loadingIndicator" style="display: none;" class="mt-3">
                    <div class="d-flex align-items-center text-primary">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">جاري الإضافة...</span>
                        </div>
                        <span>جاري إضافة الطلب...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table with per-column client-side filters -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list text-primary ms-2"></i>جدول الطلبات</h5>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="clearAllFilters">
                    <i class="fas fa-redo me-1"></i>مسح جميع الفلاتر
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="ordersTable">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>رقم الإيصال</th>
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
                                @if ($type == 'incoming')
                                    <th>تم الاستلام</th>
                                    <th>موجود</th>
                                    <th>ملاحظات</th>
                                @endif
                                <th>الإجراءات</th>
                            </tr>
                            <!-- Per-column filter row -->
                            <tr class="filter-row">
                                <th></th>
                                <th><input type="text" class="form-control form-control-sm col-filter"
                                        data-col="order_number" placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter" data-col="content"
                                        placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter" data-col="count"
                                        placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter" data-col="sender"
                                        placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter" data-col="recipient"
                                        placeholder="بحث..."></th>
                                <th>
                                    <select class="form-control form-control-sm col-filter" data-col="pay_type">
                                        <option value="">الكل</option>
                                        <option value="تحصيل">تحصيل</option>
                                        <option value="مسبق">مسبق</option>
                                    </select>
                                </th>
                                <th><input type="text" class="form-control form-control-sm col-filter" data-col="amount"
                                        placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter"
                                        data-col="anti_charger" placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter"
                                        data-col="transmitted" placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter"
                                        data-col="miscellaneous" placeholder="بحث..."></th>
                                <th><input type="text" class="form-control form-control-sm col-filter" data-col="discount"
                                        placeholder="بحث..."></th>
                                @if ($type == 'incoming')
                                    <th>
                                        <select class="form-control form-control-sm col-filter" data-col="is_paid">
                                            <option value="">الكل</option>
                                            <option value="1">نعم</option>
                                            <option value="0">لا</option>
                                        </select>
                                    </th>
                                    <th>
                                        <select class="form-control form-control-sm col-filter" data-col="is_exist">
                                            <option value="">الكل</option>
                                            <option value="1">نعم</option>
                                            <option value="0">لا</option>
                                        </select>
                                    </th>
                                    <th><input type="text" class="form-control form-control-sm col-filter" data-col="notes"
                                            placeholder="بحث..."></th>
                                @endif

                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            @forelse($orders as $order)
                                <tr id="order-row-{{ $order->id }}" data-order-number="{{ $order->order_number }}"
                                    data-content="{{ $order->content }}" data-count="{{ $order->count }}"
                                    data-sender="{{ $order->sender }}" data-recipient="{{ $order->recipient }}"
                                    data-pay-type="{{ $order->pay_type }}" data-amount="{{ $order->amount }}"
                                    data-anti-charger="{{ $order->anti_charger }}" data-transmitted="{{ $order->transmitted }}"
                                    data-miscellaneous="{{ $order->miscellaneous }}" data-discount="{{ $order->discount }}"
                                    data-paid="{{ $order->is_paid ? '1' : '0' }}"
                                    data-exist="{{ $order->is_exist ? '1' : '0' }}" data-notes="{{ $order->notes }}">
                                    <td><span
                                            class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="fw-bold">{{ $order->order_number }}</td>
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
                                    @if ($type == 'incoming')
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-paid" type="checkbox"
                                                    data-id="{{ $order->id }}" {{ $order->is_paid ? 'checked' : '' }}>
                                            </div>
                                            @if($order->paid_at)
                                                <small class="text-muted d-block">{{ $order->paid_at->format('Y-m-d H:i') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-exist" type="checkbox"
                                                    data-id="{{ $order->id }}" {{ $order->is_exist ? 'checked' : '' }}>
                                            </div>
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
                                    @endif

                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('orders.edit', $order) }}"
                                                class="btn btn-sm btn-outline-primary rounded-circle" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger rounded-circle delete-btn"
                                                data-id="{{ $order->id }}" data-number="{{ $order->order_number }}"
                                                data-menafest-id="{{ $menafest->id }}" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد طلبات</h5>
                                        <p class="text-muted">ابدأ بإضافة طلب جديد</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if(method_exists($orders, 'links'))
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->appends(request()->query())->links() }}
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

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.02);
            transition: 0.2s;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .stat-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-color: var(--primary-light);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            letter-spacing: 0.3px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            line-height: 1.2;
        }

        /* Remove spinners/arrows */
        .price-input::-webkit-outer-spin-button,
        .price-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .price-input[type=number] {
            -moz-appearance: textfield;
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
            padding: 1rem 0.75rem;
            color: #2c3e50;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }

        .bg-primary-light {
            background-color: var(--primary-light) !important;
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary-dark);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
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

        /* Filter row styling */
        .filter-row th {
            background-color: #fff !important;
            padding: 0.5rem 0.4rem !important;
            border-bottom: 2px solid var(--primary-light);
        }

        .filter-row .form-control {
            border-radius: 8px;
            font-size: 0.8rem;
            padding: 0.3rem 0.5rem;
            border: 1px solid #dee2e6;
        }

        .filter-row .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.15rem rgba(246, 190, 0, 0.25);
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
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Setup CSRF for all AJAX requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // ─── AJAX Form Submission for Quick Add ───
            $('#quickAddForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = $('#addOrderBtn');
                const originalBtnText = submitBtn.html();
                const loadingIndicator = $('#loadingIndicator');

                // Show loading state
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> جاري الإضافة...');
                submitBtn.prop('disabled', true);
                loadingIndicator.show();

                // Prepare form data - convert formatted numbers back to raw numbers
                const formData = {
                    order_number: $('#order_number').val(),
                    content: $('#content').val() || 'طرد',
                    count: $('#count').val(),
                    sender: $('#sender').val(),
                    recipient: $('#recipient').val(),
                    pay_type: $('#pay_type').val(),
                    amount: $('#amount').val().replace(/,/g, '') || 0,
                    anti_charger: $('#anti_charger').val().replace(/,/g, '') || 0,
                    transmitted: $('#transmitted').val().replace(/,/g, '') || 0,
                    miscellaneous: $('#miscellaneous').val().replace(/,/g, '') || 0,
                    discount: $('#discount').val().replace(/,/g, '') || 0,
                    notes: $('#notes').val() || '',
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            // Remove empty state if exists
                            if ($('#ordersTableBody tr td[colspan]').length) {
                                $('#ordersTableBody').empty();
                            }

                            // Add the new order to the table
                            addNewOrderToTable(response.order);

                            // Clear the form
                            clearOrderForm();

                            // Auto-focus on order number field
                            $('#order_number').focus();

                            // Update statistics
                            updateStatistics();

                            // Show success message
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });

                            // Apply current filters to the new row
                            applyAllFilters();
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'حدث خطأ أثناء إضافة الطلب';

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: errorMessage,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    complete: function () {
                        // Restore button state
                        submitBtn.html(originalBtnText);
                        submitBtn.prop('disabled', false);
                        loadingIndicator.hide();
                    }
                });
            });

            // ─── Function to Add New Order to Table ───
            function addNewOrderToTable(order) {
                // Get the current iteration number (based on visible rows + 1)
                const visibleRows = $('#ordersTableBody tr:visible').length;
                const iteration = visibleRows + 1;

                // Get the type from PHP
                const type = '{{ $type }}';

                // Build the new row HTML with your exact structure
                const newRow = `
                                                                                        <tr id="order-row-${order.id}" 
                                                                                            data-order-number="${order.order_number}"
                                                                                            data-content="${order.content || 'طرد'}"
                                                                                            data-count="${order.count}"
                                                                                            data-sender="${order.sender}"
                                                                                            data-recipient="${order.recipient}"
                                                                                            data-pay-type="${order.pay_type}"
                                                                                            data-amount="${order.amount}"
                                                                                            data-anti-charger="${order.anti_charger}"
                                                                                            data-transmitted="${order.transmitted}"
                                                                                            data-miscellaneous="${order.miscellaneous}"
                                                                                            data-discount="${order.discount}"
                                                                                            data-paid="${order.is_paid ? '1' : '0'}"
                                                                                            data-exist="${order.is_exist !== undefined ? (order.is_exist ? '1' : '0') : '1'}"
                                                                                            data-notes="${order.notes || ''}">
                                                                                            <td><span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">${iteration}</span></td>
                                                                                            <td class="fw-bold">${order.order_number}</td>
                                                                                            <td>${order.content || 'طرد'}</td>
                                                                                            <td>${formatNumber(order.count)}</td>
                                                                                            <td>${order.sender}</td>
                                                                                            <td>${order.recipient}</td>
                                                                                            <td>
                                                                                                ${order.pay_type == 'تحصيل' ?
                        '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill">تحصيل</span>' :
                        '<span class="badge bg-success text-white px-3 py-2 rounded-pill">مسبق</span>'}
                                                                                            </td>
                                                                                            <td>${formatNumber(order.amount)}</td>
                                                                                            <td>${formatNumber(order.anti_charger)}</td>
                                                                                            <td>${formatNumber(order.transmitted)}</td>
                                                                                            <td>${formatNumber(order.miscellaneous)}</td>
                                                                                            <td>${formatNumber(order.discount)}</td>
                                                                                            ${type == 'incoming' ? `
                                                                                                <td>
                                                                                                    <div class="form-check form-switch">
                                                                                                        <input class="form-check-input toggle-paid" type="checkbox"
                                                                                                            data-id="${order.id}" ${order.is_paid ? 'checked' : ''}>
                                                                                                    </div>
                                                                                                    ${order.paid_at ? `<small class="text-muted d-block">${new Date(order.paid_at).toLocaleString('ar')}</small>` : ''}
                                                                                                </td>
                                                                                                <td>
                                                                                                    <div class="form-check form-switch">
                                                                                                        <input class="form-check-input toggle-exist" type="checkbox"
                                                                                                            data-id="${order.id}" ${(order.is_exist !== undefined ? order.is_exist : true) ? 'checked' : ''}>
                                                                                                    </div>
                                                                                                </td>
                                                                                                <td>
                                                                                                <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                                                                    title="${order.notes || ''}">
                                                                                                    ${order.notes || '—'}
                                                                                                </span>
                                                                                            </td>
                                                                                            ` : ''}
                                                                                            <td>
                                                                                                <a href="/orders/${order.id}/edit"
                                                                                                    class="btn btn-sm btn-outline-primary rounded-circle" title="تعديل">
                                                                                                    <i class="fas fa-edit"></i>
                                                                                                </a>
                                                                                            </td>
                                                                                        </tr>
                                                                                    `;

                // Prepend to table body (newest first) or append based on your preference
                $('#ordersTableBody').prepend(newRow);

                // Renumber all rows
                renumberRows();
            }

            // ─── Function to Renumber Rows ───
            function renumberRows() {
                $('#ordersTableBody tr:visible').each(function (index) {
                    $(this).find('td:first .badge').text(index + 1);
                });
            }

            // ─── Function to Clear Order Form ───
            function clearOrderForm() {
                $('#quickAddForm')[0].reset();

                // Reset to defaults
                $('#content').val('طرد');
                $('#count').val(1);
                $('#pay_type').val('تحصيل');
                $('#amount').val('');
                $('#anti_charger').val('');
                $('#transmitted').val('');
                $('#miscellaneous').val('');
                $('#discount').val('');
                $('#notes').val('');
            }

            // ─── Function to Update Statistics ───
            function updateStatistics() {
                // This will be called after applyAllFilters()
                // The filter function already updates stats
            }

            // ─── Format Number Helper ───
            function formatNumber(number) {
                // Handle null or empty values
                if (number === null || number === '') {
                    return '0';
                }

                // Convert to number if it's a string
                const num = typeof number === 'string' ? parseFloat(number) : number;

                // Format with 2 decimal places first
                let formatted = num.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Remove .00 if it exists at the end
                if (formatted.slice(-3) === '.00') {
                    return formatted.slice(0, -3);
                }

                return formatted;
            }

            // Mapping from filter data-col to the row's data-attribute name
            const colToAttr = {
                'order_number': 'order-number',
                'content': 'content',
                'count': 'count',
                'sender': 'sender',
                'recipient': 'recipient',
                'pay_type': 'pay-type',
                'amount': 'amount',
                'anti_charger': 'anti-charger',
                'transmitted': 'transmitted',
                'miscellaneous': 'miscellaneous',
                'discount': 'discount',
                'is_paid': 'paid',
                'is_exist': 'exist',
                'notes': 'notes'
            };

            // Columns that use exact-match (dropdowns)
            const exactMatchCols = ['pay_type', 'is_paid', 'is_exist'];

            // ─── Per-column client-side filtering ───
            $('.col-filter').on('input change', function () {
                applyAllFilters();
            });

            // Clear all filters button
            $('#clearAllFilters').on('click', function () {
                $('.col-filter').each(function () {
                    if ($(this).is('select')) {
                        $(this).val('');
                    } else {
                        $(this).val('');
                    }
                });
                applyAllFilters();
            });

            function applyAllFilters() {
                // Collect active filters
                let filters = {};
                $('.col-filter').each(function () {
                    let col = $(this).data('col');
                    let val = $(this).val().trim();
                    if (val !== '') {
                        filters[col] = val;
                    }
                });

                // Stats accumulators
                let visibleCount = 0;
                let cashCount = 0;
                let prepaidCount = 0;
                let totalItems = 0;
                let totalCashAmount = 0;
                let totalPrepaidAmount = 0;

                $('#ordersTableBody tr').each(function () {
                    let row = $(this);

                    // Skip the "no orders" empty-state row
                    if (row.find('td[colspan]').length) return;

                    let show = true;

                    for (let col in filters) {
                        let filterVal = filters[col];
                        let attrName = colToAttr[col];
                        let cellVal = row.attr('data-' + attrName) || '';

                        if (exactMatchCols.includes(col)) {
                            // Exact match for dropdowns
                            if (cellVal !== filterVal) {
                                show = false;
                                break;
                            }
                        } else {
                            // Case-insensitive contains for text/number fields
                            if (!cellVal.toLowerCase().includes(filterVal.toLowerCase())) {
                                show = false;
                                break;
                            }
                        }
                    }

                    row.toggle(show);

                    if (show) {
                        visibleCount++;
                        let payType = row.attr('data-pay-type') || '';
                        let amount = parseFloat(row.attr('data-amount')) || 0;
                        let count = parseInt(row.attr('data-count')) || 0;

                        totalItems += count;

                        if (payType === 'تحصيل') {
                            cashCount++;
                            totalCashAmount += amount;
                        } else if (payType === 'مسبق') {
                            prepaidCount++;
                            totalPrepaidAmount += amount;
                        }
                    }
                });

                // Update stats cards to reflect filtered data
                $('#total-orders').text(visibleCount);
                $('#cash-count').text(cashCount);
                $('#prepaid-count').text(prepaidCount);
                $('#total-count').text(totalItems);
                $('#total-cash-amount').text(totalCashAmount.toFixed(2));
                $('#total-prepaid-amount').text(totalPrepaidAmount.toFixed(2));

                // Renumber visible rows
                renumberRows();
            }

            // ─── Toggle is_paid ───
            $(document).on('change', '.toggle-paid', function () {
                let checkbox = $(this);
                let orderId = checkbox.data('id');
                let wasChecked = !checkbox.is(':checked'); // value before change

                checkbox.prop('disabled', true);

                $.ajax({
                    url: '/manage-orders/' + orderId + '/toggle-paid',
                    type: 'PATCH',
                    success: function (response) {
                        if (response.success) {
                            let td = checkbox.closest('td');
                            let small = td.find('small');

                            if (response.is_paid) {
                                if (small.length === 0) {
                                    td.append('<small class="text-muted d-block">' + new Date().toLocaleString('ar') + '</small>');
                                }
                            } else {
                                small.remove();
                            }

                            // Update data attribute for filtering
                            $('#order-row-' + orderId).attr('data-paid', response.is_paid ? '1' : '0');
                            applyAllFilters();

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    },
                    error: function (xhr) {
                        // Revert on error
                        checkbox.prop('checked', wasChecked);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'حدث خطأ أثناء تحديث حالة الدفع',
                            showConfirmButton: false,
                            timer: 2000
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
                            // Update data attribute for filtering
                            $('#order-row-' + orderId).attr('data-exist', response.is_exist ? '1' : '0');
                            applyAllFilters();

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    },
                    error: function (xhr) {
                        checkbox.prop('checked', wasChecked);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'حدث خطأ أثناء تحديث حالة الوجود',
                            showConfirmButton: false,
                            timer: 2000
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

        });

        document.addEventListener('DOMContentLoaded', function () {
            // Select ALL elements with the 'price-input' class
            const priceInputs = document.querySelectorAll('.price-input');

            priceInputs.forEach(function (priceInput) {
                if (priceInput) {
                    // Format on input
                    priceInput.addEventListener('input', function (e) {
                        formatPriceInput(this);
                    });

                    // Format on blur (in case user pastes)
                    priceInput.addEventListener('blur', function (e) {
                        formatPriceInput(this);
                    });

                    // Handle initial value if any
                    if (priceInput.value) {
                        formatPriceInput(priceInput);
                    }
                }
            });

            // Format function
            function formatPriceInput(input) {
                // Remove any non-digit characters
                let value = input.value.replace(/[^\d]/g, '');

                // Format with commas
                if (value) {
                    // Remove leading zeros
                    value = parseInt(value, 10).toString();

                    // Add commas for thousands
                    input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                } else {
                    input.value = '';
                }
            }

            // Handle form submission for ALL price inputs - already handled in AJAX
            // But keep this for any non-AJAX forms
            const forms = document.querySelectorAll('form:not(#quickAddForm)');
            forms.forEach(function (form) {
                form.addEventListener('submit', function () {
                    const allPriceInputs = this.querySelectorAll('.price-input');

                    allPriceInputs.forEach(function (input) {
                        // Store original formatted value
                        const displayValue = input.value;

                        // Remove commas for submission
                        input.value = displayValue.replace(/,/g, '');
                    });

                    // Restore formatted values after a tiny delay
                    setTimeout(() => {
                        allPriceInputs.forEach(function (input) {
                            const rawValue = input.value;
                            if (rawValue) {
                                // Re-format the value
                                input.value = rawValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            }
                        });
                    }, 100);
                });
            });

            // Auto-focus on order number field on page load
            setTimeout(function () {
                document.getElementById('order_number').focus();
            }, 500);
        });

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                document.getElementById('order_number').focus();
            }, 500);
        });


        // Delete button handler
$(document).on('click', '.delete-btn', function() {
    const orderId = $(this).data('id');
    const orderNumber = $(this).data('number');
    const menafestId = $(this).data('menafest-id');
    
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
    </script>
@endpush