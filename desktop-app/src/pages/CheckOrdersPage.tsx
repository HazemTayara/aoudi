import { useState, useEffect, useRef } from 'react';
import { formatNumber, formatDateTime } from '../utils/format';
import { Order } from '../types';
import Toast from '../components/Toast';

function CheckOrdersPage() {
  const [searchNumber, setSearchNumber] = useState('');
  const [foundOrder, setFoundOrder] = useState<Order | null>(null);
  const [message, setMessage] = useState('');
  const [todayStats, setTodayStats] = useState({ total: 0, paid: 0, remaining: 0 });
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    loadTodayStats();
    inputRef.current?.focus();
  }, []);

  async function loadTodayStats() {
    const stats = await window.electronAPI.getTodayStats();
    setTodayStats(stats);
  }

  async function handleSearch(e?: React.FormEvent) {
    if (e) e.preventDefault();
    if (!searchNumber.trim()) return;

    const result = await window.electronAPI.searchOrder(searchNumber.trim());
    if (result.success && result.order) {
      setFoundOrder(result.order);
      setMessage('');
    } else {
      setFoundOrder(null);
      setMessage(result.message || 'لم يتم العثور على الطلب');
    }
  }

  async function handleMarkPaid(orderId: number) {
    const result = await window.electronAPI.markOrderPaid(orderId);
    if (result.success) {
      setToast({ show: true, message: result.message, type: 'success' });
      setFoundOrder(null);
      setSearchNumber('');
      loadTodayStats();
      inputRef.current?.focus();
    } else {
      setToast({ show: true, message: result.message, type: 'error' });
    }
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2><i className="fas fa-clipboard-check me-2"></i> تشطيب</h2>
      </div>

      {/* Today Stats */}
      <div className="stats-grid mb-4">
        <div className="stat-item">
          <div className="stat-value">{todayStats.total}</div>
          <div className="stat-label">طلبات اليوم</div>
        </div>
        <div className="stat-item">
          <div className="stat-value text-success">{todayStats.paid}</div>
          <div className="stat-label">تم التشطيب</div>
        </div>
        <div className="stat-item">
          <div className="stat-value text-danger">{todayStats.remaining}</div>
          <div className="stat-label">المتبقي</div>
        </div>
      </div>

      {/* Search Form */}
      <div className="card mb-4">
        <div className="card-body">
          <form onSubmit={handleSearch}>
            <div className="input-group">
              <input
                ref={inputRef}
                type="text"
                className="form-control check-order-input"
                placeholder="أدخل رقم الطلب..."
                value={searchNumber}
                onChange={e => setSearchNumber(e.target.value)}
                autoFocus
              />
              <button className="btn btn-primary btn-lg" type="submit">
                <i className="fas fa-search me-1"></i> بحث
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Error Message */}
      {message && !foundOrder && (
        <div className="alert alert-warning">
          <i className="fas fa-exclamation-triangle me-2"></i> {message}
        </div>
      )}

      {/* Found Order */}
      {foundOrder && (
        <div className="card order-result-card">
          <div className="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 className="mb-0">
              <i className="fas fa-box me-2"></i> طلب رقم: {foundOrder.order_number}
            </h5>
            {foundOrder.is_paid ? (
              <span className="badge bg-success fs-6">مدفوع بالفعل</span>
            ) : (
              <button className="btn btn-success btn-lg" onClick={() => handleMarkPaid(foundOrder.id)}>
                <i className="fas fa-check me-1"></i> تشطيب (تأكيد الدفع)
              </button>
            )}
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-3">
                <strong>المنفست:</strong> {foundOrder.manafest_code}
              </div>
              <div className="col-md-3">
                <strong>من:</strong> {foundOrder.from_city_name}
              </div>
              <div className="col-md-3">
                <strong>إلى:</strong> {foundOrder.to_city_name}
              </div>
              <div className="col-md-3">
                <strong>المحتوى:</strong> {foundOrder.content} ({foundOrder.count})
              </div>
              <div className="col-md-3">
                <strong>المرسل:</strong> {foundOrder.sender}
              </div>
              <div className="col-md-3">
                <strong>المرسل إليه:</strong> {foundOrder.recipient}
              </div>
              <div className="col-md-3">
                <strong>نوع الدفع:</strong>{' '}
                <span className={`badge ${foundOrder.pay_type === 'تحصيل' ? 'badge-cash' : 'badge-prepaid'}`}>
                  {foundOrder.pay_type}
                </span>
              </div>
              <div className="col-md-3">
                <strong>المبلغ:</strong> {formatNumber(foundOrder.amount)}
              </div>
              <div className="col-md-3">
                <strong>ضد الشاحن:</strong> {formatNumber(foundOrder.anti_charger)}
              </div>
              <div className="col-md-3">
                <strong>المحول:</strong> {formatNumber(foundOrder.transmitted)}
              </div>
              <div className="col-md-3">
                <strong>الوجود:</strong>{' '}
                <span className={`badge ${foundOrder.is_exist ? 'badge-exist' : 'badge-not-exist'}`}>
                  {foundOrder.is_exist ? 'موجود' : 'غير موجود'}
                </span>
              </div>
              <div className="col-md-3">
                <strong>السائق:</strong> {foundOrder.driver_name_rel || '---'}
              </div>
              <div className="col-md-6">
                <strong>ملاحظات:</strong> {foundOrder.notes || '---'}
              </div>
              <div className="col-md-3">
                <strong>تاريخ الإنشاء:</strong> {formatDateTime(foundOrder.created_at)}
              </div>
              {foundOrder.paid_at && (
                <div className="col-md-3">
                  <strong>تاريخ الدفع:</strong> {formatDateTime(foundOrder.paid_at)}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default CheckOrdersPage;
