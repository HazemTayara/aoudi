@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('menafests.orders.index', ['menafest' => $order->menafest]) }}"
                class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-right"></i> عودة
            </a>
            <h2 class="fw-bold">تعديل الطلب #{{ $order->order_number }}</h2>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('orders.update', $order) }}" method="POST" id="orderForm">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">رقم الإيصال *</label>
                            <input type="text" name="order_number" class="form-control" value="{{ $order->order_number }}"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">المحتوى</label>
                            <input type="text" name="content" class="form-control" value="{{ $order->content }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">العدد *</label>
                            <input type="text" name="count" class="form-control integer-input"
                                value="{{ $order->count ? number_format($order->count) : '' }}" required inputmode="numeric"
                                pattern="[0-9,]*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">المرسل *</label>
                            <input type="text" name="sender" class="form-control" value="{{ $order->sender }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">المرسل إليه *</label>
                            <input type="text" name="recipient" class="form-control" value="{{ $order->recipient }}"
                                required>
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
                                value="{{ $order->amount ? number_format($order->amount) : '' }}" inputmode="numeric"
                                pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">ضد الشحن</label>
                            <input type="text" name="anti_charger" class="form-control integer-input"
                                value="{{ $order->anti_charger ? number_format($order->anti_charger) : '' }}"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">المحول</label>
                            <input type="text" name="transmitted" class="form-control integer-input"
                                value="{{ $order->transmitted ? number_format($order->transmitted) : '' }}"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">متفرقات</label>
                            <input type="text" name="miscellaneous" class="form-control integer-input"
                                value="{{ $order->miscellaneous ? number_format($order->miscellaneous) : '' }}"
                                inputmode="numeric" pattern="[0-9,]*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">الخصم</label>
                            <input type="text" name="discount" class="form-control integer-input"
                                value="{{ $order->discount ? number_format($order->discount) : '' }}" inputmode="numeric"
                                pattern="[0-9,]*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $order->notes }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-5">حفظ التغييرات</button>
                            <a href="{{ route('menafests.orders.index', $order->menafest) }}"
                                class="btn btn-outline-secondary px-5">إلغاء</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Remove spinners/arrows */
        .integer-input::-webkit-outer-spin-button,
        .integer-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .integer-input[type=number] {
            -moz-appearance: textfield;
        }

        .form-control,
        .btn {
            border-radius: 12px;
        }

        /* RTL friendly number alignment */
        .integer-input {
            text-align: left;
            direction: ltr;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select all integer inputs
            const integerInputs = document.querySelectorAll('.integer-input');

            // Format function for integers only
            function formatInteger(value) {
                if (!value) return '';

                // Remove everything except digits
                let numbers = value.replace(/[^\d]/g, '');

                if (!numbers) return '';

                // Remove leading zeros
                numbers = parseInt(numbers, 10).toString();

                // Add commas for thousands
                return numbers.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            // Initialize each input
            integerInputs.forEach(input => {
                // Format on input
                input.addEventListener('input', function (e) {
                    let cursorPos = this.selectionStart;
                    let oldValue = this.value;
                    let formatted = formatInteger(this.value);

                    if (this.value !== formatted) {
                        this.value = formatted;

                        // Adjust cursor position
                        if (cursorPos) {
                            // Count digits before cursor in old value
                            let digitsBefore = (oldValue.substring(0, cursorPos).match(/\d/g) || []).length;

                            // Find new position based on digits
                            let newPos = 0;
                            let digitCount = 0;
                            while (digitCount < digitsBefore && newPos < formatted.length) {
                                if (formatted[newPos].match(/\d/)) {
                                    digitCount++;
                                }
                                newPos++;
                            }

                            this.setSelectionRange(newPos, newPos);
                        }
                    }
                });

                // Prevent non-numeric keys
                input.addEventListener('keydown', function (e) {
                    // Allow: backspace, delete, tab, escape, enter, arrows, home, end
                    if ([46, 8, 9, 27, 13, 35, 36, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                        // Allow: Ctrl+A, Ctrl+C, Ctrl+V
                        (e.keyCode === 65 && (e.ctrlKey || e.metaKey)) ||
                        (e.keyCode === 67 && (e.ctrlKey || e.metaKey)) ||
                        (e.keyCode === 86 && (e.ctrlKey || e.metaKey))) {
                        return;
                    }

                    // Ensure only digits (no decimal point)
                    if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                // Handle paste
                input.addEventListener('paste', function (e) {
                    e.preventDefault();
                    let pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    let numbersOnly = pastedText.replace(/[^\d]/g, '');

                    if (numbersOnly) {
                        let formatted = formatInteger(numbersOnly);

                        // Insert at cursor position
                        const start = this.selectionStart;
                        const end = this.selectionEnd;
                        const currentValue = this.value.replace(/,/g, '');

                        let newValue = currentValue.substring(0, start) + numbersOnly + currentValue.substring(end);
                        this.value = formatInteger(newValue);

                        // Set cursor position
                        let newCursorPos = start + numbersOnly.length;
                        let formattedBefore = this.value.substring(0, newCursorPos);
                        let commasBefore = (formattedBefore.match(/,/g) || []).length;
                        this.setSelectionRange(newCursorPos + commasBefore, newCursorPos + commasBefore);
                    }
                });

                // Format on blur (clean up)
                input.addEventListener('blur', function () {
                    if (this.value) {
                        this.value = formatInteger(this.value);
                    }
                });
            });

            // Handle form submission
            const form = document.getElementById('orderForm');
            if (form) {
                form.addEventListener('submit', function () {
                    integerInputs.forEach(input => {
                        // Remove commas for submission
                        input.value = input.value.replace(/,/g, '');
                    });
                });
            }
        });
    </script>
@endpush