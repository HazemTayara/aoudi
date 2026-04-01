import { useState, useEffect, useCallback, useRef } from 'react';
import { Order, OrderStats } from '../types';
import { formatNumber, formatDateTime } from '../utils/format';
import Pagination from '../components/Pagination';
import OrderStatsDisplay from '../components/OrderStatsDisplay';
import Toast from '../components/Toast';

function ManageOrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [stats, setStats] = useState<OrderStats | null>(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [total, setTotal] = useState(0);
  const [filters, setFilters] = useState<Record<string, string>>({});
  const [showFilters, setShowFilters] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });
  const [editingNotes, setEditingNotes] = useState<Record<number, string>>({});
  const [dirtyNotes, setDirtyNotes] = useState<Set<number>>(new Set());
  const notesRefs = useRef<Record<number, string>>({});

  const loadOrders = useCallback(async () => {
    const result = await window.electronAPI.getManageOrders({ ...filters, page, limit: 25 });
    setOrders(result.orders);
    setStats(result.stats);
    setTotalPages(result.totalPages);
    setTotal(result.total);
    // Initialize notes tracking
    const notesMap: Record<number, string> = {};
    for (const o of result.orders) {
      notesMap[o.id] = o.notes || '';
    }
    notesRefs.current = notesMap;
    setEditingNotes(notesMap);
    setDirtyNotes(new Set());
  }, [filters, page]);

  useEffect(() => { loadOrders(); }, [loadOrders]);

  function handleNoteChange(orderId: number, value: string) {
    setEditingNotes(prev => ({ ...prev, [orderId]: value }));
    const original = notesRefs.current[orderId] || '';
    setDirtyNotes(prev => {
      const next = new Set(prev);
      if (value !== original) next.add(orderId);
      else next.delete(orderId);
      return next;
    });
  }

  async function saveNote(orderId: number) {
    const notes = editingNotes[orderId] || '';
    await window.electronAPI.updateOrderNotes(orderId, notes);
    notesRefs.current[orderId] = notes;
    setDirtyNotes(prev => { const n = new Set(prev); n.delete(orderId); return n; });
    setToast({ show: true, message: 'تم حفظ الملاحظات', type: 'success' });
  }

  async function handleTogglePaid(orderId: number) {
    await window.electronAPI.toggleOrderPaid(orderId);
    loadOrders();
  }

  async function handleToggleExist(orderId: number) {
    await window.electronAPI.toggleOrderExist(orderId);
    loadOrders();
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>
          <i className="fas fa-boxes-stacked me-2"></i> إدارة الطلبات
          <small className="text-muted ms-2">({total} طلب)</small>
        </h2>
        <button className="btn btn-outline-secondary" onClick={() => setShowFilters(!showFilters)}>
          <i className="fas fa-filter me-1"></i> فلترة
        </button>
      </div>

      {stats && <OrderStatsDisplay stats={stats} />}

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
              <label className="form-label">المرسل إليه</label>
              <input className="form-control" value={filters.recipient || ''} onChange={e => setFilters(prev => ({ ...prev, recipient: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">رقم المنفست</label>
              <input className="form-control" value={filters.menafest_code || ''} onChange={e => setFilters(prev => ({ ...prev, menafest_code: e.target.value }))} />
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
              <label className="form-label">الوجود</label>
              <select className="form-select" value={filters.is_exist ?? ''} onChange={e => setFilters(prev => ({ ...prev, is_exist: e.target.value }))}>
                <option value="">الكل</option>
                <option value="1">موجود</option>
                <option value="0">غير موجود</option>
              </select>
            </div>
            <div className="col-md-3">
              <label className="form-label">السائق</label>
              <input className="form-control" value={filters.driver_name || ''} onChange={e => setFilters(prev => ({ ...prev, driver_name: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">من تاريخ</label>
              <input type="date" className="form-control" value={filters.created_from || ''} onChange={e => setFilters(prev => ({ ...prev, created_from: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">إلى تاريخ</label>
              <input type="date" className="form-control" value={filters.created_to || ''} onChange={e => setFilters(prev => ({ ...prev, created_to: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">تاريخ الدفع من</label>
              <input type="date" className="form-control" value={filters.paid_from || ''} onChange={e => setFilters(prev => ({ ...prev, paid_from: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">تاريخ الدفع إلى</label>
              <input type="date" className="form-control" value={filters.paid_to || ''} onChange={e => setFilters(prev => ({ ...prev, paid_to: e.target.value }))} />
            </div>
          </div>
          <div className="mt-3">
            <button className="btn btn-primary me-2" onClick={() => { setPage(1); loadOrders(); }}>
              <i className="fas fa-search me-1"></i> بحث
            </button>
            <button className="btn btn-outline-secondary" onClick={() => { setFilters({}); setPage(1); }}>
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
                <th>الحالة</th>
                <th>الوجود</th>
                <th>السائق</th>
                <th>ملاحظات</th>
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
                  <td>
                    <button
                      className={`btn btn-sm ${order.is_paid ? 'btn-success' : 'btn-outline-danger'}`}
                      onClick={() => handleTogglePaid(order.id)}
                    >
                      {order.is_paid ? 'مدفوع' : 'غير مدفوع'}
                    </button>
                  </td>
                  <td>
                    <button
                      className={`btn btn-sm ${order.is_exist ? 'btn-info text-white' : 'btn-outline-secondary'}`}
                      onClick={() => handleToggleExist(order.id)}
                    >
                      {order.is_exist ? 'موجود' : 'غير موجود'}
                    </button>
                  </td>
                  <td>{order.driver_name_rel || '---'}</td>
                  <td>
                    <div className="d-flex align-items-center gap-1">
                      <input
                        type="text"
                        className={`form-control form-control-sm ${dirtyNotes.has(order.id) ? 'notes-dirty' : ''}`}
                        value={editingNotes[order.id] ?? ''}
                        onChange={e => handleNoteChange(order.id, e.target.value)}
                        style={{ width: '120px' }}
                      />
                      {dirtyNotes.has(order.id) && (
                        <button className="btn btn-sm btn-warning" onClick={() => saveNote(order.id)}>
                          <i className="fas fa-save"></i>
                        </button>
                      )}
                    </div>
                  </td>
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

export default ManageOrdersPage;
