import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { Menafest, City } from '../types';
import { formatDateTime } from '../utils/format';
import Pagination from '../components/Pagination';
import Toast from '../components/Toast';

interface ManifestsPageProps {
  type: 'incoming' | 'outgoing';
}

function ManifestsPage({ type }: ManifestsPageProps) {
  const [menafests, setMenafests] = useState<Menafest[]>([]);
  const [localCity, setLocalCity] = useState<City | null>(null);
  const [cityStats, setCityStats] = useState<Record<string, number>>({});
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [total, setTotal] = useState(0);
  const [filters, setFilters] = useState<Record<string, string>>({});
  const [showFilters, setShowFilters] = useState(false);
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  const pageTitle = type === 'incoming' ? 'منافست وارد' : 'منافست صادر';

  const loadData = useCallback(async () => {
    const fetcher = type === 'incoming'
      ? window.electronAPI.getIncomingManifests
      : window.electronAPI.getOutgoingManifests;
    const result = await fetcher({ ...filters, page, limit: 10 });
    setMenafests(result.menafests);
    setLocalCity(result.localCity);
    setCityStats(result.cityStats);
    setTotalPages(result.totalPages);
    setTotal(result.total);
  }, [type, filters, page]);

  useEffect(() => { setPage(1); setFilters({}); }, [type]);
  useEffect(() => { loadData(); }, [loadData]);

  async function handleDelete(id: number) {
    if (!confirm('هل أنت متأكد من حذف هذا المنفست؟')) return;
    await window.electronAPI.deleteManifest(id);
    setToast({ show: true, message: 'تم حذف المنفست بنجاح', type: 'success' });
    loadData();
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>
          <i className={`fas ${type === 'incoming' ? 'fa-arrow-down' : 'fa-arrow-up'} me-2`}></i>
          {pageTitle}
          <small className="text-muted ms-2">({total})</small>
        </h2>
        <div>
          <button className="btn btn-outline-secondary me-2" onClick={() => setShowFilters(!showFilters)}>
            <i className="fas fa-filter me-1"></i> فلترة
          </button>
          <Link to={`/menafests/create/${type}`} className="btn btn-primary">
            <i className="fas fa-plus me-1"></i> إضافة منفست
          </Link>
        </div>
      </div>

      {!localCity && (
        <div className="alert alert-warning">
          <i className="fas fa-exclamation-triangle me-2"></i>
          الرجاء تحديد المدينة المحلية من <Link to="/settings">الإعدادات</Link> أولاً
        </div>
      )}

      {Object.keys(cityStats).length > 0 && (
        <div className="mb-3">
          {Object.entries(cityStats).map(([name, count]) => (
            <span key={name} className="city-stat-badge">
              <i className="fas fa-city"></i> {name}: {count}
            </span>
          ))}
        </div>
      )}

      {showFilters && (
        <div className="filter-section">
          <div className="row g-3">
            <div className="col-md-3">
              <label className="form-label">رقم المنفست</label>
              <input className="form-control" value={filters.manafest_code || ''} onChange={e => setFilters(prev => ({ ...prev, manafest_code: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">المدينة</label>
              <input className="form-control" value={filters.city || ''} onChange={e => setFilters(prev => ({ ...prev, city: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">السائق</label>
              <input className="form-control" value={filters.driver_name || ''} onChange={e => setFilters(prev => ({ ...prev, driver_name: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">السيارة</label>
              <input className="form-control" value={filters.car || ''} onChange={e => setFilters(prev => ({ ...prev, car: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">من تاريخ</label>
              <input type="date" className="form-control" value={filters.date_from || ''} onChange={e => setFilters(prev => ({ ...prev, date_from: e.target.value }))} />
            </div>
            <div className="col-md-3">
              <label className="form-label">إلى تاريخ</label>
              <input type="date" className="form-control" value={filters.date_to || ''} onChange={e => setFilters(prev => ({ ...prev, date_to: e.target.value }))} />
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
      )}

      <div className="card">
        <div className="card-body table-responsive">
          <table className="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>رقم المنفست</th>
                <th>{type === 'incoming' ? 'من مدينة' : 'إلى مدينة'}</th>
                <th>السائق</th>
                <th>السيارة</th>
                <th>عدد الطلبات</th>
                <th>ملاحظات</th>
                <th>تاريخ الإنشاء</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {menafests.map((m, idx) => (
                <tr key={m.id}>
                  <td>{(page - 1) * 10 + idx + 1}</td>
                  <td><strong>{m.manafest_code}</strong></td>
                  <td>{type === 'incoming' ? m.from_city_name : m.to_city_name}</td>
                  <td>{m.driver_name}</td>
                  <td>{m.car}</td>
                  <td><span className="badge bg-primary">{m.orders_count || 0}</span></td>
                  <td>{m.notes || '---'}</td>
                  <td>{formatDateTime(m.created_at)}</td>
                  <td>
                    <Link to={`/menafests/${m.id}/orders`} className="btn btn-sm btn-outline-info me-1" title="الطلبات">
                      <i className="fas fa-eye"></i>
                    </Link>
                    <Link to={`/menafests/${m.id}/edit`} className="btn btn-sm btn-outline-primary me-1" title="تعديل">
                      <i className="fas fa-edit"></i>
                    </Link>
                    <button className="btn btn-sm btn-outline-danger" onClick={() => handleDelete(m.id)} title="حذف">
                      <i className="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              ))}
              {menafests.length === 0 && (
                <tr><td colSpan={9} className="text-center text-muted py-4">لا توجد منافست</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
    </div>
  );
}

export default ManifestsPage;
