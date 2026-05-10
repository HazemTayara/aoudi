@extends('layouts.app')

@section('content')
    <div class="finishing-dashboard">
        <div class="container-fluid py-2">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    {{-- Main Search Panel --}}
                    <div class="search-panel">
                        <div class="search-panel__header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="search-icon-box">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div>
                                    <h1 class="search-panel__title">تشطيب الطلبات الواردة</h1>
                                    <p class="search-panel__subtitle">ابحث برقم الطلب أو اسم المرسل إليه</p>
                                </div>
                            </div>
                            <small class="search-panel__hint" id="shortcutHint" style="display: none;"></small>
                        </div>

                        <div class="search-panel__body">
                            <div class="search-box">
                                <input type="text" class="search-box__input" id="search_input" dir="rtl"
                                    placeholder="رقم الإيصال أو اسم المرسل إليه …" autofocus>
                                <button class="search-box__button" id="searchBtn">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                            </div>
                            <small class="search-panel__help">اضغط <kbd>Enter</kbd> للبحث السريع</small>
                        </div>
                    </div>

                    {{-- Loading --}}
                    <div id="loading" class="text-center my-4 d-none">
                        <div class="spinner-border" style="color: #F6BE00;" role="status">
                            <span class="visually-hidden">جاري البحث...</span>
                        </div>
                    </div>

                    {{-- Single Order Card --}}
                    <div id="orderCard" class="modern-order-card d-none">
                        <div class="modern-order-card__header">
                            <div class="modern-order-card__badges">
                                <span class="order-badge order-badge--id" id="orderNumber"></span>
                                <span class="order-badge order-badge--manifest" id="manifestCode"></span>
                            </div>
                            <div class="modern-order-card__actions">
                                <a href="#" id="editOrderBtn" class="btn-soft btn-soft--light d-none">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                            </div>
                        </div>

                        <div class="modern-order-card__body">
                            {{-- City & manifest highlight --}}
                            <div class="highlight-row">
                                <div class="highlight-item">
                                    <span class="highlight-label">📦 كود المنفيست</span>
                                    <span class="highlight-value" id="manifestCodeValue"></span>
                                </div>
                                <div class="highlight-item">
                                    <span class="highlight-label">🏙️ من مدينة</span>
                                    <span class="highlight-value" id="fromCity"></span>
                                </div>
                                <div class="highlight-item">
                                    <span class="highlight-label">🕒 تاريخ الإنشاء</span>
                                    <span class="highlight-value" id="createdAt"></span>
                                </div>
                            </div>

                            {{-- Details grid --}}
                            <div class="details-grid">
                                <div class="detail-cell"><label>المحتوى</label><span id="content"></span></div>
                                <div class="detail-cell"><label>العدد</label><span id="count"></span></div>
                                <div class="detail-cell"><label>المرسل</label><span id="sender"></span></div>
                                <div class="detail-cell"><label>المستلم</label><span id="recipient"></span></div>
                                <div class="detail-cell"><label>نوع الدفع</label><span id="payType"></span></div>
                                <div class="detail-cell"><label>المبلغ</label><span id="amount"></span></div>
                                <div class="detail-cell"><label>ضد الدفع</label><span id="antiCharger"></span></div>
                                <div class="detail-cell"><label>محول</label><span id="transmitted"></span></div>
                                <div class="detail-cell"><label>السائق</label><span id="driverName"></span></div>
                                <div class="detail-cell"><label>ملاحظات</label><span id="notes"></span></div>
                                <div class="detail-cell"><label>تاريخ التسليم للسائق</label><span id="assignedAt"></span>
                                </div>
                            </div>

                            {{-- Payment footer --}}
                            <div class="payment-footer">
                                <div class="payment-status">
                                    <span>حالة الدفع</span>
                                    <span id="paymentStatus" class="status-badge status-badge--unpaid">غير مدفوع</span>
                                </div>
                                <div class="payment-action">
                                    <small class="shortcut-hint" id="shortcutKeyHint" style="display: none;">
                                        اضغط <kbd>Enter</kbd>
                                    </small>
                                    <button id="markPaidBtn" class="btn-pay">
                                        <i class="fas fa-check-circle"></i> تأكيد الدفع
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Multiple Orders Table --}}
                    <div id="ordersTableCard" class="modern-table-container d-none">
                        <div class="modern-table-container__header">
                            <div>
                                <i class="fas fa-list"></i>
                                <span>نتائج البحث – <strong id="resultsCount"></strong> طلبات</span>
                            </div>
                            <span class="search-badge" id="searchTermDisplay"></span>
                        </div>
                        <div class="table-responsive">
                            <table class="modern-table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>رقم الإيصال</th>
                                        <th>كود المنفيست</th>
                                        <th>المرسل</th>
                                        <th>المرسل إليه</th>
                                        <th>العدد</th>
                                        <th>نوع الدفع</th>
                                        <th>المبلغ</th>
                                        <th>ضد الشحن</th>
                                        <th>محول</th>
                                        <th>متفرقات</th>
                                        <th>السائق</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>تاريخ التسليم</th>
                                        <th>حالة الدفع</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="ordersTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Not Found --}}
                    <div id="notFoundCard" class="empty-state d-none">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد نتائج</h5>
                        <p class="text-muted" id="notFoundMessage"></p>
                        <button class="btn-soft btn-soft--primary" onclick="resetAndFocus()">
                            <i class="fas fa-redo"></i> بحث جديد
                        </button>
                    </div>

                    {{-- Message Alert --}}
                    <div id="messageAlert" class="alert-modern d-none mt-2" role="alert"></div>

                    {{-- Quick Stats --}}
                    <div class="stats-row">
                        <div class="stat-card">
                            <span class="stat-card__icon">💰</span>
                            <div>
                                <div class="stat-card__label">مدفوع اليوم</div>
                                <div class="stat-card__value text-success" id="todayPaid">-</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-card__icon">📦</span>
                            <div>
                                <div class="stat-card__label">إجمالي وارد اليوم</div>
                                <div class="stat-card__value" style="color:#F6BE00;" id="todayTotal">-</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary: #F6BE00;
            --primary-dark: #e5a800;
            --primary-light: #FFF3CD;
        }

        .finishing-dashboard {
            background: #f4f7fc;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ---- Search Panel ---- */
        .search-panel {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .search-panel__header {
            background: linear-gradient(135deg, var(--primary) 0%, #FFD54F 100%);
            padding: 1rem 1.5rem;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .search-icon-box {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .search-panel__title {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .search-panel__subtitle {
            font-size: 0.8rem;
            opacity: 0.8;
            margin: 0.1rem 0 0 0;
        }

        .search-panel__hint {
            background: rgba(255, 255, 255, 0.6);
            padding: 0.2rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.75rem;
        }

        .search-panel__body {
            padding: 1.2rem 1.5rem;
        }

        .search-box {
            display: flex;
            gap: 0.5rem;
            background: #f0f3f8;
            border-radius: 2.5rem;
            padding: 0.4rem;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .search-box:focus-within {
            background: #e8ecf3;
            box-shadow: 0 0 0 2px rgba(246, 190, 0, 0.3);
        }

        .search-box__input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.6rem 1.2rem;
            font-size: 0.95rem;
            text-align: right;
            direction: rtl;
            outline: none;
            border-radius: 2rem;
        }

        .search-box__button {
            background: var(--primary);
            border: none;
            color: #1e293b;
            padding: 0.6rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .search-box__button:hover {
            background: var(--primary-dark);
            color: white;
        }

        .search-panel__help {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* ---- Single Order Card ---- */
        .modern-order-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 1.5rem;
            animation: fadeUp 0.3s ease;
        }

        .modern-order-card__header {
            background: #FFFDF5;
            padding: 0.7rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #F6BE00;
        }

        .order-badge {
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .order-badge--id {
            background: #FFF3CD;
            color: #856404;
        }

        .order-badge--manifest {
            background: #FFF8E1;
            color: #B45309;
        }

        .btn-soft {
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.8rem;
        }

        .btn-soft--light {
            background: #ffffff;
            color: #333;
            border: 1px solid #dee2e6;
        }

        .btn-soft--light:hover {
            background: #f1f3f5;
        }

        .btn-soft--primary {
            background: var(--primary);
            color: #1e293b;
            border: none;
        }

        .modern-order-card__body {
            padding: 1rem 1.5rem 1.5rem;
        }

        .highlight-row {
            display: flex;
            gap: 1.5rem;
            background: #FFFDF5;
            border-radius: 0.8rem;
            padding: 1rem;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
        }

        .highlight-item {
            flex: 1;
            min-width: 120px;
        }

        .highlight-label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.2rem;
        }

        .highlight-value {
            font-weight: 700;
            font-size: 0.95rem;
            color: #2c3e50;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .detail-cell {
            background: #f7f9fc;
            padding: 0.7rem 1rem;
            border-radius: 0.6rem;
        }

        .detail-cell label {
            display: block;
            font-size: 0.7rem;
            color: #7c8a97;
            margin-bottom: 0.2rem;
        }

        .detail-cell span {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }

        /* Payment footer */
        .payment-footer {
            background: #FFFDF5;
            border-radius: 0.8rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .payment-status {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
        }

        .status-badge--paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge--unpaid {
            background: #FFF3CD;
            color: #856404;
        }

        .btn-pay {
            background: var(--primary);
            color: #1e293b;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(246, 190, 0, 0.4);
        }

        .btn-pay:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(246, 190, 0, 0.5);
        }

        .btn-pay:disabled {
            background: #9ca3af;
            box-shadow: none;
            cursor: not-allowed;
        }

        .shortcut-hint {
            font-size: 0.8rem;
            color: #6b7280;
        }

        /* ---- Table Container ---- */
        .modern-table-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .modern-table-container__header {
            background: #FFFDF5;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            color: #856404;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #F6BE00;
        }

        .search-badge {
            background: white;
            padding: 0.2rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            color: #374151;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }

        .modern-table thead th {
            background: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            padding: 0.5rem 0.4rem;
            white-space: nowrap;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .modern-table tbody td {
            padding: 0.4rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            text-align: right;
            white-space: nowrap;
        }

        .modern-table tbody tr:hover {
            background: #FFFDF5;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
        }

        /* Alert Modern */
        .alert-modern {
            background: white;
            border-radius: 0.8rem;
            padding: 0.8rem 1.2rem;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.04);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 0.8rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card__icon {
            font-size: 1.8rem;
        }

        .stat-card__label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .stat-card__value {
            font-size: 1.4rem;
            font-weight: 700;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .highlight-row {
                flex-direction: column;
                gap: 0.8rem;
            }

            .details-grid {
                grid-template-columns: 1fr 1fr;
            }

            .payment-footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helpers
            function formatNumber(number) {
                if (number === null || number === '') return '0';
                const num = typeof number === 'string' ? parseFloat(number) : number;
                let formatted = num.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (formatted.slice(-3) === '.00') return formatted.slice(0, -3);
                return formatted;
            }

            function formatDateArabic(dateString) {
                if (!dateString) return '—';
                const date = new Date(dateString);
                return date.toLocaleDateString('ar-SY', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });
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
                messageAlert.className = 'alert-modern d-none mt-2';
                if (type === 'success') messageAlert.style.background = '#d1fae5';
                else if (type === 'danger') messageAlert.style.background = '#fee2e2';
                else messageAlert.style.background = '#FFF3CD';
                messageAlert.classList.remove('d-none');
                setTimeout(() => messageAlert.classList.add('d-none'), 3500);
            }

            function resetAndFocus() {
                searchInput.value = '';
                searchInput.focus();
                orderCard.classList.add('d-none');
                ordersTableCard.classList.add('d-none');
                notFoundCard.classList.add('d-none');
                messageAlert.classList.add('d-none');
                isSingleResult = false;
                updateShortcutHints();
            }
            window.resetAndFocus = resetAndFocus;

            function updateShortcutHints() {
                if (isSingleResult && !currentOrderIsPaid) {
                    shortcutHint.style.display = 'block';
                    shortcutHint.textContent = 'Enter = تأكيد الدفع';
                    shortcutKeyHint.style.display = 'inline';
                } else if (isSingleResult && currentOrderIsPaid) {
                    shortcutHint.style.display = 'block';
                    shortcutHint.textContent = 'تم الدفع';
                    shortcutKeyHint.style.display = 'none';
                } else {
                    shortcutHint.style.display = 'none';
                    shortcutKeyHint.style.display = 'none';
                }
            }

            function searchOrder() {
                const searchTerm = searchInput.value.trim();
                if (!searchTerm) { showMessage('الرجاء إدخال رقم الطلب أو اسم المرسل إليه', 'warning'); return; }
                if (searchTerm.length < 2) { showMessage('الرجاء إدخال حرفين على الأقل', 'warning'); return; }

                orderCard.classList.add('d-none');
                ordersTableCard.classList.add('d-none');
                notFoundCard.classList.add('d-none');
                messageAlert.classList.add('d-none');
                loading.classList.remove('d-none');
                isSingleResult = false;

                fetch(`/orders/search?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('d-none');
                        if (data.success) {
                            if (data.type === 'single') displaySingleOrder(data.order);
                            else displayMultipleOrders(data.orders, data.count, searchTerm);
                        } else {
                            document.getElementById('notFoundMessage').textContent = data.message || 'تأكد من البيانات';
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
                document.getElementById('manifestCode').textContent = order.menafest?.manafest_code || '—';
                document.getElementById('manifestCodeValue').textContent = order.menafest?.manafest_code || '—';
                document.getElementById('fromCity').textContent = order.menafest?.from_city?.name || '—';

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
                document.getElementById('createdAt').textContent = formatDateArabic(order.created_at);
                document.getElementById('assignedAt').textContent = order.assigned_at ? formatDateArabic(order.assigned_at) : 'لم يتم بعد';

                const paymentStatus = document.getElementById('paymentStatus');
                if (order.is_paid) {
                    paymentStatus.textContent = 'مدفوع في ' + (order.paid_at ? formatDateArabic(order.paid_at) : '');
                    paymentStatus.className = 'status-badge status-badge--paid';
                    markPaidBtn.disabled = true;
                } else {
                    paymentStatus.textContent = 'غير مدفوع';
                    paymentStatus.className = 'status-badge status-badge--unpaid';
                    markPaidBtn.disabled = false;
                }

                const editBtn = document.getElementById('editOrderBtn');
                editBtn.href = `/orders/${order.id}/edit?return_url=${encodeURIComponent(window.location.href)}`;
                editBtn.classList.remove('d-none');

                orderCard.classList.remove('d-none');
                updateShortcutHints();
                if (!order.is_paid) markPaidBtn.focus();
            }

            function displayMultipleOrders(orders, count, searchTerm) {
                document.getElementById('resultsCount').textContent = count;
                document.getElementById('searchTermDisplay').textContent = `بحث: ${searchTerm}`;
                const tbody = document.getElementById('ordersTableBody');
                tbody.innerHTML = '';

                orders.forEach((order, index) => {
                    const paidHtml = order.is_paid
                        ? '<span class="badge bg-success">مدفوع</span>'
                        : `<button class="btn btn-sm btn-warning pay-btn" data-id="${order.id}" data-order="${order.order_number}" style="background:#F6BE00; border:none; color:#1e293b;"><i class="fas fa-check"></i> تأكيد</button>`;

                    const row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td class="fw-bold">${order.order_number}</td>
                            <td>${order.menafest?.manafest_code || '—'} | ${order.menafest?.from_city?.name || '—'}</td>
                            <td>${order.sender || '—'}</td>
                            <td>${order.recipient || '—'}</td>
                            <td>(${order.count || '—'}) ${order.content || '—'}</td>
                            <td>${order.pay_type === 'تحصيل' ? '<span class="badge bg-warning text-dark">تحصيل</span>' : '<span class="badge bg-success">مسبق</span>'}</td>
                            <td>${formatNumber(order.amount)}</td>
                            <td>${order.anti_charger ? formatNumber(order.anti_charger) : '—'}</td>
                            <td>${order.transmitted ? formatNumber(order.transmitted) : '—'}</td>
                            <td>${order.miscellaneous ? formatNumber(order.miscellaneous) : '—'}</td>
                            <td>${order.driver?.name || '—'} <br> ${order.assigned_at ? formatDateArabic(order.assigned_at) : '—'}</td>
                            <td>${formatDateArabic(order.created_at)}</td>
                            <td>${order.paid_at ? formatDateArabic(order.paid_at) : '—'}</td>
                            <td id="payment-cell-${order.id}">${paidHtml}</td>
                            <td><a href="/orders/${order.id}/edit?return_url=${encodeURIComponent(window.location.href)}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });

                document.querySelectorAll('.pay-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        markOrderAsPaidFromTable(this.dataset.id, this.dataset.order, this);
                    });
                });

                ordersTableCard.classList.remove('d-none');
            }

            function markAsPaid() {
                if (!currentOrderId || currentOrderIsPaid) return;
                markPaidBtn.disabled = true;
                const originalHtml = markPaidBtn.innerHTML;
                markPaidBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري التحديث...';

                fetch('{{ route("orders.mark-paid") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order_id: currentOrderId })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('✓ تم تأكيد الدفع بنجاح', 'success');
                            currentOrderIsPaid = true;
                            document.getElementById('paymentStatus').textContent = 'مدفوع';
                            document.getElementById('paymentStatus').className = 'status-badge status-badge--paid';
                            markPaidBtn.disabled = true;
                            updateShortcutHints();
                            updateStatsAfterPayment();
                            setTimeout(() => { resetAndFocus(); orderCard.classList.add('d-none'); }, 1500);
                        } else {
                            showMessage(data.message, 'danger');
                            markPaidBtn.disabled = false;
                        }
                    })
                    .catch(() => {
                        showMessage('حدث خطأ', 'danger');
                        markPaidBtn.disabled = false;
                    })
                    .finally(() => { markPaidBtn.innerHTML = originalHtml; });
            }

            function markOrderAsPaidFromTable(orderId, orderNumber, btn) {
                btn.disabled = true;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                fetch('{{ route("orders.mark-paid") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(`✓ تم تأكيد دفع الطلب #${orderNumber}`, 'success');
                            const cell = document.getElementById(`payment-cell-${orderId}`);
                            if (cell) cell.innerHTML = '<span class="badge bg-success">مدفوع</span>';
                            updateStatsAfterPayment();
                        } else {
                            showMessage(data.message, 'danger');
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    })
                    .catch(() => {
                        showMessage('حدث خطأ', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
            }

            function updateStatsAfterPayment() {
                const paidEl = document.getElementById('todayPaid');
                const totalEl = document.getElementById('todayTotal');
                if (paidEl) paidEl.textContent = (parseInt(paidEl.textContent) || 0) + 1;
                if (totalEl) totalEl.textContent = (parseInt(totalEl.textContent) || 0) + 1;
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && isSingleResult && !currentOrderIsPaid) {
                    const active = document.activeElement;
                    if (active === markPaidBtn || active === document.body || active === searchInput) {
                        if (active === searchInput) e.preventDefault();
                        e.preventDefault();
                        markAsPaid();
                    }
                }
                if (e.key === 'Escape') resetAndFocus();
            });

            searchBtn.addEventListener('click', searchOrder);
            searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); searchOrder(); } });
            markPaidBtn.addEventListener('click', markAsPaid);

            fetch('/orders/today-stats?type=incoming')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('todayPaid').textContent = data.paid || '0';
                    document.getElementById('todayTotal').textContent = data.total || '0';
                })
                .catch(() => { });

            searchInput.focus();
        });
    </script>
@endpush