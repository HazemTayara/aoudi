@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-right"></i> عودة
                </a>
                <h2 class="fw-bold">تعديل الطلب #{{ $order->order_number }}</h2>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('orders.update', $order) }}" method="POST" id="orderForm">
                        @csrf
                        @method('PUT')

                        <!-- Driver Assignment Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="mb-3"><i class="fas fa-user-tie text-primary ms-2"></i>إسناد السائق</h5>
                                        <div class="row align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">اختر السائق</label>
                                                <select name="driver_id" class="form-select" id="driverSelect">
                                                    <option value="">بدون سائق</option>
                                                    @foreach($drivers as $driver)
                                                        <option value="{{ $driver->id }}" 
                                                            {{ $order->driver_id == $driver->id ? 'selected' : '' }}>
                                                            {{ $driver->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-2">
                                                    <small class="text-muted">السائق الحالي:</small>
                                                    <strong>{{ $order->driver ? $order->driver->name : 'غير مسند' }}</strong>
                                                </div>
                                                @if($order->assigned_at)
                                                    <small class="text-muted">
                                                        تاريخ الإسناد: {{ $order->assigned_at->format('Y-m-d H:i') }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if($order->driver_id)
                                                    <button type="button" class="btn btn-outline-danger" id="unassignDriverBtn">
                                                        <i class="fas fa-unlink"></i> فك الإسناد
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1"><i class="fas fa-money-bill text-success ms-2"></i>حالة الدفع</h5>
                                                <div class="mt-2">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                            name="is_paid" id="isPaidSwitch" value="1"
                                                            {{ $order->is_paid ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="isPaidSwitch">
                                                            <span id="paymentStatusText">
                                                                {{ $order->is_paid ? 'مدفوع' : 'غير مدفوع' }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                    @if($order->paid_at)
                                                        <small class="text-muted d-block mt-1">
                                                            تاريخ الدفع: {{ $order->paid_at->format('Y-m-d H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <span class="badge {{ $order->is_paid ? 'bg-success' : 'bg-warning' }} p-3" 
                                                    id="paymentStatusBadge">
                                                    {{ $order->is_paid ? 'تم الدفع' : 'لم يتم الدفع' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details Section -->
                        <h5 class="mb-3"><i class="fas fa-info-circle text-primary ms-2"></i>تفاصيل الطلب</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">رقم الإيصال *</label>
                                <input type="text" name="order_number" class="form-control" value="{{ $order->order_number }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">المحتوى</label>
                                <input type="text" name="content" class="form-control" value="{{ $order->content }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">العدد *</label>
                                <input type="text" name="count" class="form-control integer-input" 
                                    value="{{ $order->count ? number_format($order->count) : '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">المرسل *</label>
                                <input type="text" name="sender" class="form-control" value="{{ $order->sender }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">المرسل إليه *</label>
                                <input type="text" name="recipient" class="form-control" value="{{ $order->recipient }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">نوع الدفع *</label>
                                <select name="pay_type" class="form-control" required>
                                    <option value="تحصيل" {{ $order->pay_type == 'تحصيل' ? 'selected' : '' }}>تحصيل</option>
                                    <option value="مسبق" {{ $order->pay_type == 'مسبق' ? 'selected' : '' }}>مسبق</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المبلغ</label>
                                <input type="text" name="amount" class="form-control integer-input" 
                                    value="{{ $order->amount ? number_format($order->amount) : '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">ضد الشحن</label>
                                <input type="text" name="anti_charger" class="form-control integer-input" 
                                    value="{{ $order->anti_charger ? number_format($order->anti_charger) : '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">المحول</label>
                                <input type="text" name="transmitted" class="form-control integer-input" 
                                    value="{{ $order->transmitted ? number_format($order->transmitted) : '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">متفرقات</label>
                                <input type="text" name="miscellaneous" class="form-control integer-input" 
                                    value="{{ $order->miscellaneous ? number_format($order->miscellaneous) : '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">الخصم</label>
                                <input type="text" name="discount" class="form-control integer-input" 
                                    value="{{ $order->discount ? number_format($order->discount) : '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3">{{ $order->notes }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-5">حفظ التغييرات</button>
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-5">إلغاء</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

@push('styles')
    <style>
        .integer-input::-webkit-outer-spin-button,
        .integer-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .integer-input[type=number] {
            -moz-appearance: textfield;
        }
        .form-control, .btn, .form-select {
            border-radius: 12px;
        }
        .integer-input {
            text-align: left;
            direction: ltr;
        }
        .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Payment status toggle
            const isPaidSwitch = document.getElementById('isPaidSwitch');
            const paymentStatusText = document.getElementById('paymentStatusText');
            const paymentStatusBadge = document.getElementById('paymentStatusBadge');

            if (isPaidSwitch) {
                isPaidSwitch.addEventListener('change', function() {
                    if (this.checked) {
                        paymentStatusText.textContent = 'مدفوع';
                        paymentStatusBadge.textContent = 'تم الدفع';
                        paymentStatusBadge.className = 'badge bg-success p-3';
                    } else {
                        paymentStatusText.textContent = 'غير مدفوع';
                        paymentStatusBadge.textContent = 'لم يتم الدفع';
                        paymentStatusBadge.className = 'badge bg-warning p-3';
                    }
                });
            }

            // Unassign driver button
            const unassignBtn = document.getElementById('unassignDriverBtn');
            const driverSelect = document.getElementById('driverSelect');

            if (unassignBtn) {
                unassignBtn.addEventListener('click', function() {
                    driverSelect.value = '';
                });
            }

            // Integer input formatting
            const integerInputs = document.querySelectorAll('.integer-input');

            function formatInteger(value) {
                if (!value) return '';
                let numbers = value.replace(/[^\d]/g, '');
                if (!numbers) return '';
                numbers = parseInt(numbers, 10).toString();
                return numbers.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            integerInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let cursorPos = this.selectionStart;
                    let oldValue = this.value;
                    let formatted = formatInteger(this.value);

                    if (this.value !== formatted) {
                        this.value = formatted;
                        if (cursorPos) {
                            let digitsBefore = (oldValue.substring(0, cursorPos).match(/\d/g) || []).length;
                            let newPos = 0;
                            let digitCount = 0;
                            while (digitCount < digitsBefore && newPos < formatted.length) {
                                if (formatted[newPos].match(/\d/)) digitCount++;
                                newPos++;
                            }
                            this.setSelectionRange(newPos, newPos);
                        }
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if ([46, 8, 9, 27, 13, 35, 36, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                        (e.keyCode === 65 && (e.ctrlKey || e.metaKey)) ||
                        (e.keyCode === 67 && (e.ctrlKey || e.metaKey)) ||
                        (e.keyCode === 86 && (e.ctrlKey || e.metaKey))) {
                        return;
                    }
                    if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    let pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    let numbersOnly = pastedText.replace(/[^\d]/g, '');
                    if (numbersOnly) {
                        let formatted = formatInteger(numbersOnly);
                        const start = this.selectionStart;
                        const end = this.selectionEnd;
                        const currentValue = this.value.replace(/,/g, '');
                        let newValue = currentValue.substring(0, start) + numbersOnly + currentValue.substring(end);
                        this.value = formatInteger(newValue);
                        let newCursorPos = start + numbersOnly.length;
                        let formattedBefore = this.value.substring(0, newCursorPos);
                        let commasBefore = (formattedBefore.match(/,/g) || []).length;
                        this.setSelectionRange(newCursorPos + commasBefore, newCursorPos + commasBefore);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.value = formatInteger(this.value);
                    }
                });
            });

            // Form submission
            const form = document.getElementById('orderForm');
            if (form) {
                form.addEventListener('submit', function() {
                    integerInputs.forEach(input => {
                        input.value = input.value.replace(/,/g, '');
                    });
                });
            }
        });
    </script>
@endpush