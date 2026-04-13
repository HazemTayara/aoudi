import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Order, City, OrderStats } from '../types';
import { formatNumber, formatDateTime } from '../utils/format';
import Pagination from '../components/Pagination';
import OrderStatsDisplay from '../components/OrderStatsDisplay';

function CityOrdersPage() {
  const { id } = useParams<{ id: string }>();
  const [orders, setOrders] = useState<Order[]>([]);
  const [city, setCity] = useState<City | null>(null);
  const [stats, setStats] = useState<OrderStats | null>(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [total, setTotal] = useState(0);
  const [filters, setFilters] = useState<Record<string, string>>({});
  const [showFilters, setShowFilters] = useState(false);

  const loadOrders = useCallback(async () => {
    if (!id) return;
    const result = await window.electronAPI.getCityOrders(Number(id), { ...filters, page, limit: 25 });
    setOrders(result.orders);
    setCity(result.city);
    setStats(result.stats);
    setTotalPages(result.totalPages);
    setTotal(result.total);
  }, [id, filters, page]);

  useEffect(() => { loadOrders(); }, [loadOrders]);

  function handleFilterChange(key: string, value: string) {
    setFilters(prev => ({ ...prev, [key]: value }));
  }

  function applyFilters() {
    setPage(1);
    loadOrders();
  }

  function clearFilters() {
    setFilters({});
    setPage(1);
  }

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>
          <i className="fas fa-city me-2"></i>
          طلبات مدينة: {city?.name || '...'}
          <small className="text-muted ms-2">({total} طلب)</small>
        </h2>
        <div>
          <button className="btn btn-outline-secondary me-2" onClick={() => setShowFilters(!showFilters)}>
            <i className="fas fa-filter me-1"></i> فلترة
          </button>
          <Link to="/cities" className="btn btn-outline-dark">
            <i className="fas fa-arrow-right me-1"></i> رجوع
          </Link>
        </div>
      </div>

      {stats && <OrderStatsDisplay stats={stats} />}

      {showFilters && (
        <div className="filter-section">
          <div className="row g-3">
            <div className="col-md-3">
              <label className="form-label">رقم الطلب</label>
              <input className="form-control" value={filters.order_number || ''} onChange={e => handleFilterChange('order_number', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">المرسل</label>
              <input className="form-control" value={filters.sender || ''} onChange={e => handleFilterChange('sender', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">المرسل إليه</label>
              <input className="form-control" value={filters.recipient || ''} onChange={e => handleFilterChange('recipient', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">نوع الدفع</label>
              <select className="form-select" value={filters.pay_type || ''} onChange={e => handleFilterChange('pay_type', e.target.value)}>
                <option value="">الكل</option>
                <option value="تحصيل">تحصيل</option>
                <option value="مسبق">مسبق</option>
              </select>
            </div>
            <div className="col-md-3">
              <label className="form-label">حالة الدفع</label>
              <select className="form-select" value={filters.is_paid ?? ''} onChange={e => handleFilterChange('is_paid', e.target.value)}>
                <option value="">الكل</option>
                <option value="1">مدفوع</option>
                <option value="0">غير مدفوع</option>
              </select>
            </div>
            <div className="col-md-3">
              <label className="form-label">الوجود</label>
              <select className="form-select" value={filters.is_exist ?? ''} onChange={e => handleFilterChange('is_exist', e.target.value)}>
                <option value="">الكل</option>
                <option value="1">موجود</option>
                <option value="0">غير موجود</option>
              </select>
            </div>
            <div className="col-md-3">
              <label className="form-label">من تاريخ</label>
              <input type="date" className="form-control" value={filters.created_from || ''} onChange={e => handleFilterChange('created_from', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">إلى تاريخ</label>
              <input type="date" className="form-control" value={filters.created_to || ''} onChange={e => handleFilterChange('created_to', e.target.value)} />
            </div>
          </div>
          <div className="mt-3">
            <button className="btn btn-primary me-2" onClick={applyFilters}>
              <i className="fas fa-search me-1"></i> بحث
            </button>
            <button className="btn btn-outline-secondary" onClick={clearFilters}>
              <i className="fas fa-times me-1"></i> مسح الفلاتر
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
                <th>المرسل</th>
                <th>المرسل إليه</th>
                <th>الدفع</th>
                <th>المبلغ</th>
                <th>ضد الشاحن</th>
                <th>الحالة</th>
                <th>الوجود</th>
                <th>السائق</th>
                <th>تاريخ الإنشاء</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((order, idx) => (
                <tr key={order.id}>
                  <td>{(page - 1) * 25 + idx + 1}</td>
                  <td><strong>{order.order_number}</strong></td>
                  <td>{order.manafest_code}</td>
                  <td>{order.sender}</td>
                  <td>{order.recipient}</td>
                  <td>
                    <span className={`badge ${order.pay_type === 'تحصيل' ? 'badge-cash' : 'badge-prepaid'}`}>
                      {order.pay_type}
                    </span>
                  </td>
                  <td>{formatNumber(order.amount)}</td>
                  <td>{formatNumber(order.anti_charger)}</td>
                  <td>
                    <span className={`badge ${order.is_paid ? 'badge-paid' : 'badge-unpaid'}`}>
                      {order.is_paid ? 'مدفوع' : 'غير مدفوع'}
                    </span>
                  </td>
                  <td>
                    <span className={`badge ${order.is_exist ? 'badge-exist' : 'badge-not-exist'}`}>
                      {order.is_exist ? 'موجود' : 'غير موجود'}
                    </span>
                  </td>
                  <td>{order.driver_name_rel || '---'}</td>
                  <td>{formatDateTime(order.created_at)}</td>
                </tr>
              ))}
              {orders.length === 0 && (
                <tr><td colSpan={12} className="text-center text-muted py-4">لا توجد طلبات</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
    </div>
  );
}

export default CityOrdersPage;
