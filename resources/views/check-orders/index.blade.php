@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-search-dollar"></i> تشطيب
                            <small class="text-white-50 ms-2" id="shortcutHint"
                                style="display: none; font-size: 0.7em;"></small>
                        </h4>
                    </div>
                    <div class="card-body">
                        {{-- Unified Search Input --}}
                        <div class="mb-4">
                            <label for="search_input" class="form-label">بحث عن طلب</label>
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control" id="search_input"
                                    placeholder="أدخل رقم الطلب أو اسم المرسل إليه ثم اضغط Enter..." autofocus>
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                            <small class="text-muted">يمكنك البحث برقم الإيصال أو اسم المرسل إليه</small>
                        </div>

                        {{-- Loading Indicator --}}
                        <div id="loading" class="text-center my-4 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري البحث...</span>
                            </div>
                        </div>

                        {{-- Single Order Card --}}
                        <div id="orderCard" class="card mb-4 d-none">
                            <div
                                class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-box"></i> تفاصيل الطلب
                                </span>
                                <span>
                                    <span class="badge bg-light text-dark ms-2" id="orderNumber"></span>
                                    <span class="badge bg-info" id="manifestCode"></span>
                                    <a href="#" id="editOrderBtn" class="btn btn-sm btn-light ms-2" style="display: none;">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
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
                                                    <div class="d-flex align-items-center gap-3">
                                                        <small class="text-muted" id="shortcutKeyHint"
                                                            style="display: none;">
                                                            اضغط <kbd>Enter</kbd> لتأكيد الدفع
                                                        </small>
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

                        {{-- Multiple Orders Table --}}
                        <div id="ordersTableCard" class="card d-none">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-list"></i>
                                    نتائج البحث - <span id="resultsCount"></span> طلبات
                                </h5>
                                <span class="badge bg-light text-dark" id="searchTermDisplay"></span>
                            </div>
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
                                                <th>السائق</th>
                                                <th>حالة الدفع</th>
                                                <th>إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ordersTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Not Found Card --}}
                        <div id="notFoundCard" class="card d-none">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">لم يتم العثور على طلبات</h5>
                                <p class="text-muted" id="notFoundMessage">تأكد من رقم الطلب أو اسم المرسل إليه وحاول مرة
                                    أخرى</p>
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
                            <div class="col-6">
                                <div class="text-muted">مدفوع اليوم</div>
                                <div class="h2 text-success" id="todayPaid">-</div>
                            </div>
                            {{-- <div class="col-6">
                                <div class="text-muted">متبقي اليوم</div>
                                <div class="h2 text-warning" id="todayRemaining">-</div>
                            </div> --}}
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
            function formatNumber(number) {
                if (number === null || number === '') return '0';
                const num = typeof number === 'string' ? parseFloat(number) : number;
                let formatted = num.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (formatted.slice(-3) === '.00') {
                    return formatted.slice(0, -3);
                }
                return formatted;
            }

            const searchInput = document.getElementById('search_input');
            const searchBtn = document.getElementById('searchBtn');
            const loading = document.getElementById('loading');
            const orderCard = document.getElementById('orderCard');
            const ordersTableCard = document.getElementById('ordersTableCard');
            const notFoundCard = document.getElementById('notFoundCard');
            const markPaidBtn = document.getElementById('markPaidBtn');
            const messageAlert = document.getElementById('messageAlert');
            const shortcutHint = document.getElementById('shortcutHint');
            const shortcutKeyHint = document.getElementById('shortcutKeyHint');

            let currentOrderId = null;
            let currentOrderIsPaid = false;
            let isSingleResult = false;

            function showMessage(message, type = 'success') {
                messageAlert.textContent = message;
                messageAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
                messageAlert.classList.add(`alert-${type}`);
                setTimeout(() => {
                    messageAlert.classList.add('d-none');
                }, 3000);
            }

            function resetAndFocus() {
                searchInput.value = '';
                searchInput.focus();
                orderCard.classList.add('d-none');
                ordersTableCard.classList.add('d-none');
                notFoundCard.classList.add('d-none');
                isSingleResult = false;
                updateShortcutHints();
            }

            window.resetAndFocus = resetAndFocus;

            function updateShortcutHints() {
                if (isSingleResult && !currentOrderIsPaid) {
                    shortcutHint.style.display = 'inline';
                    shortcutHint.textContent = '(Enter = تأكيد الدفع)';
                    shortcutKeyHint.style.display = 'inline';
                } else if (isSingleResult && currentOrderIsPaid) {
                    shortcutHint.style.display = 'inline';
                    shortcutHint.textContent = '(تم الدفع)';
                    shortcutKeyHint.style.display = 'none';
                } else {
                    shortcutHint.style.display = 'none';
                    shortcutKeyHint.style.display = 'none';
                }
            }

            function searchOrder() {
                const searchTerm = searchInput.value.trim();

                if (!searchTerm) {
                    showMessage('الرجاء إدخال رقم الطلب أو اسم المرسل إليه', 'warning');
                    return;
                }

                if (searchTerm.length < 2) {
                    showMessage('الرجاء إدخال حرفين على الأقل للبحث', 'warning');
                    return;
                }

                orderCard.classList.add('d-none');
                ordersTableCard.classList.add('d-none');
                notFoundCard.classList.add('d-none');
                loading.classList.remove('d-none');
                messageAlert.classList.add('d-none');
                isSingleResult = false;

                fetch(`/orders/search?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('d-none');
                        if (data.success) {
                            if (data.type === 'single') {
                                displaySingleOrder(data.order);
                            } else {
                                displayMultipleOrders(data.orders, data.count, searchTerm);
                            }
                        } else {
                            document.getElementById('notFoundMessage').textContent = data.message || 'تأكد من رقم الطلب أو اسم المرسل إليه وحاول مرة أخرى';
                            notFoundCard.classList.remove('d-none');
                        }
                        updateShortcutHints();
                    })
                    .catch(error => {
                        loading.classList.add('d-none');
                        showMessage('حدث خطأ في الاتصال', 'danger');
                        console.error(error);
                    });
            }

            function displaySingleOrder(order) {
                currentOrderId = order.id;
                currentOrderIsPaid = order.is_paid;
                isSingleResult = true;

                document.getElementById('orderNumber').textContent = order.order_number;
                document.getElementById('manifestCode').textContent = `منفست: ${order.menafest?.manafest_code || '—'}`;
                document.getElementById('fromCity').textContent = order.menafest?.from_city?.name || '—';
                document.getElementById('toCity').textContent = order.menafest?.to_city?.name || '—';
                document.getElementById('content').textContent = order.content || '—';
                document.getElementById('count').textContent = order.count || '—';
                document.getElementById('sender').textContent = order.sender || '—';
                document.getElementById('recipient').textContent = order.recipient || '—';
                document.getElementById('payType').textContent = order.pay_type || '—';
                document.getElementById('amount').textContent = order.amount ? formatNumber(order.amount) : '—';
                document.getElementById('antiCharger').textContent = order.anti_charger ? formatNumber(order.anti_charger) : '—';
                document.getElementById('transmitted').textContent = order.transmitted ? formatNumber(order.transmitted) : '—';
                document.getElementById('driverName').textContent = order.driver?.name || '—';
                document.getElementById('notes').textContent = order.notes || '—';

                const paymentStatus = document.getElementById('paymentStatus');
                if (order.is_paid) {
                    paymentStatus.textContent = 'مدفوع';
                    paymentStatus.className = 'badge bg-success p-2';
                    markPaidBtn.disabled = true;
                } else {
                    paymentStatus.textContent = 'غير مدفوع';
                    paymentStatus.className = 'badge bg-warning p-2';
                    markPaidBtn.disabled = false;
                }

                const editBtn = document.getElementById('editOrderBtn');
                const currentUrl = window.location.href;
                editBtn.href = `/orders/${order.id}/edit?return_url=${encodeURIComponent(currentUrl)}`;
                editBtn.style.display = 'inline-block';

                orderCard.classList.remove('d-none');
                updateShortcutHints();

                // Focus the mark paid button for easy Enter key access
                if (!order.is_paid) {
                    markPaidBtn.focus();
                }
            }

            function displayMultipleOrders(orders, count, searchTerm) {
                document.getElementById('resultsCount').textContent = count;
                document.getElementById('searchTermDisplay').textContent = `بحث: ${searchTerm}`;

                const tbody = document.getElementById('ordersTableBody');
                tbody.innerHTML = '';

                orders.forEach((order, index) => {
                    const paidButtonHtml = order.is_paid
                        ? '<span class="badge bg-success">مدفوع</span>'
                        : `<button type="button" class="btn btn-success btn-sm rounded-pill pay-btn" 
                                    data-id="${order.id}" data-order="${order.order_number}"
                                    title="تأكيد الدفع">
                                    <i class="fas fa-check"></i> تأكيد
                               </button>`;

                    const row = `
                            <tr id="order-row-${order.id}">
                                <td><span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">${index + 1}</span></td>
                                <td class="fw-bold">${order.order_number}</td>
                                <td><small>${order.menafest?.manafest_code || '—'} | ${order.menafest?.from_city?.name || '—'}</small></td>
                                <td>${order.sender || '—'}</td>
                                <td>${order.recipient || '—'}</td>
                                <td>
                                    ${order.pay_type === 'تحصيل'
                            ? '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill">تحصيل</span>'
                            : '<span class="badge bg-success text-white px-3 py-2 rounded-pill">مسبق</span>'}
                                </td>
                                <td>${formatNumber(order.amount)}</td>
                                <td>${order.driver?.name || '—'}</td>
                                <td id="payment-cell-${order.id}">
                                    ${paidButtonHtml}
                                </td>
                                <td>
                                    <a href="/orders/${order.id}/edit?return_url=${encodeURIComponent(window.location.href)}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });

                // Attach event listeners to pay buttons
                document.querySelectorAll('.pay-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const orderId = this.dataset.id;
                        const orderNumber = this.dataset.order;
                        markOrderAsPaidFromTable(orderId, orderNumber, this);
                    });
                });

                ordersTableCard.classList.remove('d-none');
            }

            function markAsPaid() {
                if (!currentOrderId || currentOrderIsPaid) return;

                markPaidBtn.disabled = true;
                const originalText = markPaidBtn.innerHTML;
                markPaidBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري التحديث...';

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
                            currentOrderIsPaid = true;
                            document.getElementById('paymentStatus').textContent = 'مدفوع';
                            document.getElementById('paymentStatus').className = 'badge bg-success p-2';
                            markPaidBtn.disabled = true;
                            updateShortcutHints();
                            updateStatsAfterPayment();

                            // Clear after 1.5 seconds
                            setTimeout(() => {
                                resetAndFocus();
                                orderCard.classList.add('d-none');
                            }, 1500);
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

            function markOrderAsPaidFromTable(orderId, orderNumber, buttonElement) {
                // Disable the button
                buttonElement.disabled = true;
                const originalHtml = buttonElement.innerHTML;
                buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch('{{ route("orders.mark-paid") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(`✓ تم تأكيد دفع الطلب #${orderNumber}`, 'success');
                            // Update the cell to show paid status
                            const cell = document.getElementById(`payment-cell-${orderId}`);
                            if (cell) {
                                cell.innerHTML = '<span class="badge bg-success">مدفوع</span>';
                            }
                            updateStatsAfterPayment();
                        } else {
                            showMessage(data.message, 'danger');
                            buttonElement.disabled = false;
                            buttonElement.innerHTML = originalHtml;
                        }
                    })
                    .catch(error => {
                        showMessage('حدث خطأ', 'danger');
                        buttonElement.disabled = false;
                        buttonElement.innerHTML = originalHtml;
                    });
            }

            function updateStatsAfterPayment() {
                const todayPaidEl = document.getElementById('todayPaid');
                // const todayRemainingEl = document.getElementById('todayRemaining');
                let currentPaid = parseInt(todayPaidEl.textContent) || 0;
                // let currentRemaining = parseInt(todayRemainingEl.textContent) || 0;
                todayPaidEl.textContent = currentPaid + 1;
                // todayRemainingEl.textContent = Math.max(0, currentRemaining - 1);
            }

            // ─── Keyboard Shortcuts ───
            document.addEventListener('keydown', function (e) {
                // Enter key for confirming payment (only when single result is showing)
                if (e.key === 'Enter' && isSingleResult && !currentOrderIsPaid) {
                    // Make sure we're not typing in an input field (except the search input)
                    const activeElement = document.activeElement;
                    if (activeElement === markPaidBtn ||
                        activeElement === document.body ||
                        activeElement === searchInput && e.ctrlKey === false && e.metaKey === false) {

                        // If focus is on search input, prevent form submission
                        if (activeElement === searchInput) {
                            e.preventDefault();
                        }

                        if (currentOrderId && !currentOrderIsPaid) {
                            e.preventDefault();
                            markAsPaid();
                        }
                    }
                }

                // Escape key to reset
                if (e.key === 'Escape') {
                    resetAndFocus();
                }
            });

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
                    // document.getElementById('todayRemaining').textContent = data.remaining || '0';
                })
                .catch(() => { });

            searchInput.focus();
        });
    </script>
@endpush