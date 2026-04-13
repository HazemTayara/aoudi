import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Order, Driver } from '../types';
import { formatNumber } from '../utils/format';
import Pagination from '../components/Pagination';
import Toast from '../components/Toast';

function DriverAttachPage() {
  const { id } = useParams<{ id: string }>();
  const [orders, setOrders] = useState<Order[]>([]);
  const [driver, setDriver] = useState<Driver | null>(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [total, setTotal] = useState(0);
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [filters, setFilters] = useState<Record<string, string>>({});
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  const loadData = useCallback(async () => {
    if (!id) return;
    const [driverData, ordersData] = await Promise.all([
      window.electronAPI.getDriver(Number(id)),
      window.electronAPI.getUnassignedOrders({ ...filters, page, limit: 50 }),
    ]);
    setDriver(driverData);
    setOrders(ordersData.orders);
    setTotalPages(ordersData.totalPages);
    setTotal(ordersData.total);
  }, [id, filters, page]);

  useEffect(() => { loadData(); }, [loadData]);

  function toggleSelect(orderId: number) {
    setSelectedIds(prev => {
      const next = new Set(prev);
      if (next.has(orderId)) next.delete(orderId);
      else next.add(orderId);
      return next;
    });
  }

  function toggleSelectAll() {
    if (selectedIds.size === orders.length) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(orders.map(o => o.id)));
    }
  }

  async function handleAttach() {
    if (!id || selectedIds.size === 0) return;
    const result = await window.electronAPI.attachOrdersToDriver(Number(id), Array.from(selectedIds));
    if (result.success) {
      setToast({ show: true, message: `تم إسناد ${result.count} طلب بنجاح`, type: 'success' });
      setSelectedIds(new Set());
      loadData();
    }
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>
          <i className="fas fa-link me-2"></i>
          إسناد طلبات للسائق: {driver?.name || '...'}
          <small className="text-muted ms-2">({total} طلب متاح)</small>
        </h2>
        <Link to="/drivers" className="btn btn-outline-dark">
          <i className="fas fa-arrow-right me-1"></i> رجوع
        </Link>
      </div>

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
            <label className="form-label">المرسل إليه</label>
            <input className="form-control" value={filters.recipient || ''} onChange={e => setFilters(prev => ({ ...prev, recipient: e.target.value }))} />
          </div>
          <div className="col-md-3">
            <label className="form-label">نوع الدفع</label>
            <select className="form-select" value={filters.pay_type || ''} onChange={e => setFilters(prev => ({ ...prev, pay_type: e.target.value }))}>
              <option value="">الكل</option>
              <option value="تحصيل">تحصيل</option>
              <option value="مسبق">مسبق</option>
            </select>
          </div>
        </div>
        <div className="mt-3">
          <button className="btn btn-primary me-2" onClick={() => { setPage(1); loadData(); }}>
            <i className="fas fa-search me-1"></i> بحث
          </button>
          <button className="btn btn-outline-secondary" onClick={() => { setFilters({}); setPage(1); }}>
            <i className="fas fa-times me-1"></i> مسح
          </button>
        </div>
      </div>

      <div className="card">
        <div className="card-body table-responsive">
          <table className="table table-hover table-sm">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" checked={selectedIds.size === orders.length && orders.length > 0} onChange={toggleSelectAll} />
                </th>
                <th>#</th>
                <th>رقم الطلب</th>
                <th>المنفست</th>
                <th>من</th>
                <th>إلى</th>
                <th>المرسل</th>
                <th>المرسل إليه</th>
                <th>الدفع</th>
                <th>المبلغ</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((order, idx) => (
                <tr key={order.id} className={selectedIds.has(order.id) ? 'table-primary' : ''}>
                  <td>
                    <input type="checkbox" checked={selectedIds.has(order.id)} onChange={() => toggleSelect(order.id)} />
                  </td>
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
                </tr>
              ))}
              {orders.length === 0 && (
                <tr><td colSpan={10} className="text-center text-muted py-4">لا توجد طلبات غير مسندة</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />

      {selectedIds.size > 0 && (
        <div className="sticky-action-bar">
          <div className="d-flex justify-content-between align-items-center">
            <span className="fw-bold">تم اختيار {selectedIds.size} طلب</span>
            <button className="btn btn-success btn-lg" onClick={handleAttach}>
              <i className="fas fa-link me-1"></i> إسناد الطلبات المحددة
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default DriverAttachPage;
