import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Driver } from '../types';
import Pagination from '../components/Pagination';
import Toast from '../components/Toast';

function DriversPage() {
  const [drivers, setDrivers] = useState<Driver[]>([]);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [showModal, setShowModal] = useState(false);
  const [editDriver, setEditDriver] = useState<Driver | null>(null);
  const [driverName, setDriverName] = useState('');
  const [driverNotes, setDriverNotes] = useState('');
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  useEffect(() => { loadDrivers(); }, [page]);

  async function loadDrivers() {
    const result = await window.electronAPI.getDrivers(page, 10);
    setDrivers(result.drivers);
    setTotalPages(result.totalPages);
  }

  function openCreate() {
    setEditDriver(null);
    setDriverName('');
    setDriverNotes('');
    setShowModal(true);
  }

  function openEdit(driver: Driver) {
    setEditDriver(driver);
    setDriverName(driver.name);
    setDriverNotes(driver.notes || '');
    setShowModal(true);
  }

  async function handleSave() {
    if (!driverName.trim()) return;
    if (editDriver) {
      await window.electronAPI.updateDriver(editDriver.id, { name: driverName.trim(), notes: driverNotes.trim() || undefined });
      setToast({ show: true, message: 'تم تعديل السائق بنجاح', type: 'success' });
    } else {
      await window.electronAPI.createDriver({ name: driverName.trim(), notes: driverNotes.trim() || undefined });
      setToast({ show: true, message: 'تم إضافة السائق بنجاح', type: 'success' });
    }
    setShowModal(false);
    loadDrivers();
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2><i className="fas fa-truck me-2"></i> السائقين</h2>
        <button className="btn btn-primary" onClick={openCreate}>
          <i className="fas fa-plus me-1"></i> إضافة سائق
        </button>
      </div>

      <div className="card">
        <div className="card-body">
          <table className="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>اسم السائق</th>
                <th>ملاحظات</th>
                <th>عدد الطلبات</th>
                <th>تاريخ الإنشاء</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {drivers.map((driver, idx) => (
                <tr key={driver.id}>
                  <td>{(page - 1) * 10 + idx + 1}</td>
                  <td><strong>{driver.name}</strong></td>
                  <td>{driver.notes || '---'}</td>
                  <td><span className="badge bg-primary">{driver.orders_count || 0}</span></td>
                  <td>{new Date(driver.created_at).toLocaleDateString('en-CA')}</td>
                  <td>
                    <button className="btn btn-sm btn-outline-primary me-1" onClick={() => openEdit(driver)}>
                      <i className="fas fa-edit"></i>
                    </button>
                    <Link to={`/drivers/${driver.id}/orders`} className="btn btn-sm btn-outline-info me-1">
                      <i className="fas fa-eye"></i> الطلبات
                    </Link>
                    <Link to={`/drivers/${driver.id}/attach`} className="btn btn-sm btn-outline-success">
                      <i className="fas fa-link"></i> إسناد
                    </Link>
                  </td>
                </tr>
              ))}
              {drivers.length === 0 && (
                <tr><td colSpan={6} className="text-center text-muted py-4">لا يوجد سائقين</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />

      {showModal && (
        <div className="modal d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">{editDriver ? 'تعديل سائق' : 'إضافة سائق جديد'}</h5>
                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
              </div>
              <div className="modal-body">
                <div className="mb-3">
                  <label className="form-label">اسم السائق</label>
                  <input type="text" className="form-control" value={driverName} onChange={e => setDriverName(e.target.value)} autoFocus />
                </div>
                <div className="mb-3">
                  <label className="form-label">ملاحظات</label>
                  <textarea className="form-control" value={driverNotes} onChange={e => setDriverNotes(e.target.value)} rows={3} />
                </div>
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowModal(false)}>إلغاء</button>
                <button className="btn btn-primary" onClick={handleSave}>
                  {editDriver ? 'تعديل' : 'إضافة'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default DriversPage;
