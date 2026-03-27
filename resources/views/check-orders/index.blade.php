@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-search-dollar"></i>
                            تشطيب
                        </h4>
                    </div>
                    <div class="card-body">
                        {{-- Search Input --}}
                        <div class="mb-4">
                            <label for="order_search" class="form-label">رقم الطلب</label>
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control" id="order_search"
                                    placeholder="أدخل رقم الطلب ثم اضغط Enter" autofocus>
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                        </div>

                        {{-- Loading Indicator --}}
                        <div id="loading" class="text-center my-4 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري البحث...</span>
                            </div>
                        </div>

                        {{-- Order Details Card --}}
                        <div id="orderCard" class="card mb-4 d-none">
                            <div
                                class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-box"></i> تفاصيل الطلب
                                </span>
                                <span>
                                    <span class="badge bg-light text-dark ms-2" id="orderNumber"></span>
                                    <span class="badge bg-info" id="manifestCode"></span>
                                </span>
                            </div>
                            <div class="card-body">
                                {{-- City Info --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="border-start pe-3">
                                            <small class="text-muted d-block">من مدينة</small>
                                            <strong id="fromCity"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border-start pe-3">
                                            <small class="text-muted d-block">إلى مدينة</small>
                                            <strong id="toCity"></strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Order Details Grid --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">المحتوى</small>
                                            <strong id="content"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">العدد</small>
                                            <strong id="count"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">المرسل</small>
                                            <strong id="sender"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">المستلم</small>
                                            <strong id="recipient"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">نوع الدفع</small>
                                            <strong id="payType"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">المبلغ</small>
                                            <strong id="amount"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">ضد الدفع</small>
                                            <strong id="antiCharger"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">محول</small>
                                            <strong id="transmitted"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">السائق</small>
                                            <strong id="driverName"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">ملاحظات</small>
                                            <strong id="notes"></strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Section --}}
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">حالة الدفع</h6>
                                                        <span id="paymentStatus" class="badge bg-warning p-2">غير
                                                            مدفوع</span>
                                                    </div>
                                                    <div>
                                                        <button id="markPaidBtn" class="btn btn-success btn-lg">
                                                            <i class="fas fa-check-circle"></i> تأكيد الدفع
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Not Found Card (hidden initially) --}}
                        <div id="notFoundCard" class="card d-none">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">لم يتم العثور على طلب</h5>
                                <p class="text-muted">تأكد من رقم الطلب وحاول مرة أخرى</p>
                                <button class="btn btn-outline-primary" onclick="resetAndFocus()">
                                    <i class="fas fa-redo"></i> بحث جديد
                                </button>
                            </div>
                        </div>

                        {{-- Message Alert --}}
                        <div id="messageAlert" class="alert d-none mt-3" role="alert"></div>
                    </div>
                </div>

                {{-- Quick Stats Card --}}
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="text-muted">مدفوع اليوم</div>
                                <div class="h2 text-success" id="todayPaid">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Number formatting function
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

            const searchInput = document.getElementById('order_search');
            const searchBtn = document.getElementById('searchBtn');
            const loading = document.getElementById('loading');
            const orderCard = document.getElementById('orderCard');
            const notFoundCard = document.getElementById('notFoundCard');
            const markPaidBtn = document.getElementById('markPaidBtn');
            const messageAlert = document.getElementById('messageAlert');

            // Spans for data
            const orderNumberSpan = document.getElementById('orderNumber');
            const manifestCodeSpan = document.getElementById('manifestCode');
            const fromCitySpan = document.getElementById('fromCity');
            const toCitySpan = document.getElementById('toCity');
            const paymentStatus = document.getElementById('paymentStatus');

            // Fields mapping - including antiCharger and transmitted
            const fields = [
                'content', 'count', 'sender', 'recipient', 'payType', 'amount',
                'antiCharger', 'transmitted', 'driverName', 'notes'
            ];
            fields.forEach(field => {
                window[field] = document.getElementById(field);
            });

            let currentOrderId = null;

            // Show message function
            function showMessage(message, type = 'success') {
                messageAlert.textContent = message;
                messageAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
                messageAlert.classList.add(`alert-${type}`);
                setTimeout(() => {
                    messageAlert.classList.add('d-none');
                }, 3000);
            }

            // Reset search function
            function resetAndFocus() {
                searchInput.value = '';
                searchInput.focus();
                orderCard.classList.add('d-none');
                notFoundCard.classList.add('d-none');
            }

            window.resetAndFocus = resetAndFocus; // Make it global for the button

            // Search function
            function searchOrder() {
                const orderNumber = searchInput.value.trim();
                if (!orderNumber) {
                    showMessage('الرجاء إدخال رقم الطلب', 'warning');
                    return;
                }

                // Hide cards and show loading
                orderCard.classList.add('d-none');
                notFoundCard.classList.add('d-none');
                loading.classList.remove('d-none');
                messageAlert.classList.add('d-none');

                // Fetch order
                fetch(`/orders/search?number=${encodeURIComponent(orderNumber)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('d-none');
                        if (data.success) {
                            displayOrder(data.order);
                        } else {
                            notFoundCard.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        loading.classList.add('d-none');
                        showMessage('حدث خطأ في الاتصال', 'danger');
                        console.error(error);
                    });
            }

            // Display order function - updated to use formatNumber
            // Display order function - updated to use formatNumber without currency
            function displayOrder(order) {
                currentOrderId = order.id;

                // Basic info
                orderNumberSpan.textContent = order.order_number;
                manifestCodeSpan.textContent = `منفست: ${order.menafest?.manafest_code || '—'}`;

                // Cities
                fromCitySpan.textContent = order.menafest?.from_city?.name || '—';
                toCitySpan.textContent = order.menafest?.to_city?.name || '—';

                // Order fields
                content.textContent = order.content || '—';
                count.textContent = order.count || '—';
                sender.textContent = order.sender || '—';
                recipient.textContent = order.recipient || '—';
                payType.textContent = order.pay_type || '—';

                // Amount fields with number formatting
                amount.textContent = order.amount ? formatNumber(order.amount) : '—';
                antiCharger.textContent = order.anti_charger ? formatNumber(order.anti_charger) : '—';
                transmitted.textContent = order.transmitted ? formatNumber(order.transmitted) : '—';

                // Driver name and notes
                driverName.textContent = order.driver?.name || '—';
                notes.textContent = order.notes || '—';

                // Payment status
                if (order.is_paid) {
                    paymentStatus.textContent = 'مدفوع';
                    paymentStatus.className = 'badge bg-success p-2';
                    markPaidBtn.disabled = true;
                } else {
                    paymentStatus.textContent = 'غير مدفوع';
                    paymentStatus.className = 'badge bg-warning p-2';
                    markPaidBtn.disabled = false;
                }

                orderCard.classList.remove('d-none');
            }

            // Mark as paid function
            function markAsPaid() {
                if (!currentOrderId) return;

                markPaidBtn.disabled = true;
                const originalText = markPaidBtn.innerHTML;
                markPaidBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري التحديث...';

                fetch('{{ route("orders.mark-paid") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order_id: currentOrderId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('✓ تم تأكيد الدفع بنجاح', 'success');
                            // Update UI
                            paymentStatus.textContent = 'مدفوع';
                            paymentStatus.className = 'badge bg-success p-2';
                            markPaidBtn.disabled = true;

                            // Update stats immediately
                            updateStatsAfterPayment();

                            // Clear for next order after 1 second
                            setTimeout(() => {
                                resetAndFocus();
                                orderCard.classList.add('d-none');
                            }, 1000);
                        } else {
                            showMessage(data.message, 'danger');
                            markPaidBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        showMessage('حدث خطأ', 'danger');
                        markPaidBtn.disabled = false;
                    })
                    .finally(() => {
                        markPaidBtn.innerHTML = originalText;
                    });
            }

            // Function to update stats after payment
            function updateStatsAfterPayment() {
                // Get current stats
                const todayPaidEl = document.getElementById('todayPaid');

                // Increment paid count
                let currentPaid = parseInt(todayPaidEl.textContent) || 0;
                todayPaidEl.textContent = currentPaid + 1;
            }

            // Event listeners
            searchBtn.addEventListener('click', searchOrder);
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchOrder();
                }
            });
            markPaidBtn.addEventListener('click', markAsPaid);

            // Load today's stats
            fetch('/orders/today-stats?type=incoming')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('todayPaid').textContent = data.paid || '0';
                })
                .catch(() => {
                    // Silently fail, not critical
                });

            // Initial focus
            searchInput.focus();
        });
    </script>
@endpush