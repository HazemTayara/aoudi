import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Order, Driver } from '../types';
import { formatNumber, formatDateTime } from '../utils/format';
import Pagination from '../components/Pagination';
import Toast from '../components/Toast';

function DriverOrdersPage() {
  const { id } = useParams<{ id: string }>();
  const [orders, setOrders] = useState<Order[]>([]);
  const [driver, setDriver] = useState<Driver | null>(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [total, setTotal] = useState(0);
  const [filters, setFilters] = useState<Record<string, string>>({});
  const [showFilters, setShowFilters] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  const loadOrders = useCallback(async () => {
    if (!id) return;
    const result = await window.electronAPI.getDriverOrders(Number(id), { ...filters, page, limit: 50 });
    setOrders(result.orders);
    setDriver(result.driver);
    setTotalPages(result.totalPages);
    setTotal(result.total);
  }, [id, filters, page]);

  useEffect(() => { loadOrders(); }, [loadOrders]);

  async function handleDetach(orderId: number) {
    if (!id) return;
    const result = await window.electronAPI.detachOrderFromDriver(Number(id), orderId);
    if (result.success) {
      setToast({ show: true, message: 'تم فك إسناد الطلب بنجاح', type: 'success' });
      loadOrders();
    } else {
      setToast({ show: true, message: result.message || 'حدث خطأ', type: 'error' });
    }
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>
          <i className="fas fa-truck me-2"></i>
          طلبات السائق: {driver?.name || '...'}
          <small className="text-muted ms-2">({total} طلب)</small>
        </h2>
        <div>
          <button className="btn btn-outline-secondary me-2" onClick={() => setShowFilters(!showFilters)}>
            <i className="fas fa-filter me-1"></i> فلترة
          </button>
          <Link to="/drivers" className="btn btn-outline-dark">
            <i className="fas fa-arrow-right me-1"></i> رجوع
          </Link>
        </div>
      </div>

      {showFilters && (
        <div className="filter-section">
          <div className="row g-3">
            <div className="col-md-3">
              <label className="form-label">رقم الطلب</label>
              <input className="form-control" value={filters.order_number || ''} onChange={e => setFilters(prev => ({ ...prev, order_number: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">المرسل</label>
              <input className="form-control" value={filters.sender || ''} onChange={e => setFilters(prev => ({ ...prev, sender: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">نوع الدفع</label>
              <select className="form-select" value={filters.pay_type || ''} onChange={e => setFilters(prev => ({ ...prev, pay_type: e.target.value }))}>
                <option value="">الكل</option>
                <option value="تحصيل">تحصيل</option>
                <option value="مسبق">مسبق</option>
              </select>
            </div>
            <div className="col-md-3">
              <label className="form-label">حالة الدفع</label>
              <select className="form-select" value={filters.is_paid ?? ''} onChange={e => setFilters(prev => ({ ...prev, is_paid: e.target.value }))}>
                <option value="">الكل</option>
                <option value="1">مدفوع</option>
                <option value="0">غير مدفوع</option>
              </select>
            </div>
            <div className="col-md-3">
              <label className="form-label">تاريخ الإسناد من</label>
              <input type="date" className="form-control" value={filters.assigned_from || ''} onChange={e => setFilters(prev => ({ ...prev, assigned_from: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">تاريخ الإسناد إلى</label>
              <input type="date" className="form-control" value={filters.assigned_to || ''} onChange={e => setFilters(prev => ({ ...prev, assigned_to: e.target.value }))} />
            </div>
          </div>
          <div className="mt-3">
            <button className="btn btn-primary me-2" onClick={() => { setPage(1); loadOrders(); }}>
              <i className="fas fa-search me-1"></i> بحث
            </button>
            <button className="btn btn-outline-secondary" onClick={() => { setFilters({}); setPage(1); }}>
              <i className="fas fa-times me-1"></i> مسح
            </button>
          </div>
        </div>
      )}

      <div className="card">
        <div className="card-body table-responsive">
          <table className="table table-hover table-sm">
            <thead>
              <tr>
                <th>#</th>
                <th>رقم الطلب</th>
                <th>المنفست</th>
                <th>من</th>
                <th>إلى</th>
                <th>المرسل</th>
                <th>المرسل إليه</th>
                <th>الدفع</th>
                <th>المبلغ</th>
                <th>الحالة</th>
                <th>تاريخ الإسناد</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((order, idx) => (
                <tr key={order.id}>
                  <td>{(page - 1) * 50 + idx + 1}</td>
                  <td><strong>{order.order_number}</strong></td>
                  <td>{order.manafest_code}</td>
                  <td>{order.from_city_name}</td>
                  <td>{order.to_city_name}</td>
                  <td>{order.sender}</td>
                  <td>{order.recipient}</td>
                  <td>
                    <span className={`badge ${order.pay_type === 'تحصيل' ? 'badge-cash' : 'badge-prepaid'}`}>
                      {order.pay_type}
                    </span>
                  </td>
                  <td>{formatNumber(order.amount)}</td>
                  <td>
                    <span className={`badge ${order.is_paid ? 'badge-paid' : 'badge-unpaid'}`}>
                      {order.is_paid ? 'مدفوع' : 'غير مدفوع'}
                    </span>
                  </td>
                  <td>{formatDateTime(order.assigned_at)}</td>
                  <td>
                    <button className="btn btn-sm btn-outline-danger" onClick={() => handleDetach(order.id)} title="فك الإسناد">
                      <i className="fas fa-unlink"></i>
                    </button>
                  </td>
                </tr>
              ))}
              {orders.length === 0 && (
                <tr><td colSpan={12} className="text-center text-muted py-4">لا توجد طلبات مسندة</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
    </div>
  );
}

export default DriverOrdersPage;
