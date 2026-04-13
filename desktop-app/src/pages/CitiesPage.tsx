import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { City } from '../types';
import Pagination from '../components/Pagination';
import Toast from '../components/Toast';

function CitiesPage() {
  const [cities, setCities] = useState<City[]>([]);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [showModal, setShowModal] = useState(false);
  const [editCity, setEditCity] = useState<City | null>(null);
  const [cityName, setCityName] = useState('');
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  useEffect(() => { loadCities(); }, [page]);

  async function loadCities() {
    const result = await window.electronAPI.getCities(page, 10);
    setCities(result.cities);
    setTotalPages(result.totalPages);
  }

  function openCreate() {
    setEditCity(null);
    setCityName('');
    setShowModal(true);
  }

  function openEdit(city: City) {
    setEditCity(city);
    setCityName(city.name);
    setShowModal(true);
  }

  async function handleSave() {
    if (!cityName.trim()) return;
    if (editCity) {
      await window.electronAPI.updateCity(editCity.id, { name: cityName.trim() });
      setToast({ show: true, message: 'تم تعديل المدينة بنجاح', type: 'success' });
    } else {
      await window.electronAPI.createCity({ name: cityName.trim() });
      setToast({ show: true, message: 'تم إضافة المدينة بنجاح', type: 'success' });
    }
    setShowModal(false);
    loadCities();
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2><i className="fas fa-city me-2"></i> المدن</h2>
        <button className="btn btn-primary" onClick={openCreate}>
          <i className="fas fa-plus me-1"></i> إضافة مدينة
        </button>
      </div>

      <div className="card">
        <div className="card-body">
          <table className="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>اسم المدينة</th>
                <th>محلية</th>
                <th>تاريخ الإنشاء</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {cities.map((city, idx) => (
                <tr key={city.id}>
                  <td>{(page - 1) * 10 + idx + 1}</td>
                  <td><strong>{city.name}</strong></td>
                  <td>
                    {city.is_local ? (
                      <span className="badge bg-success">محلية</span>
                    ) : (
                      <span className="badge bg-secondary">خارجية</span>
                    )}
                  </td>
                  <td>{new Date(city.created_at).toLocaleDateString('en-CA')}</td>
                  <td>
                    <button className="btn btn-sm btn-outline-primary me-1" onClick={() => openEdit(city)}>
                      <i className="fas fa-edit"></i>
                    </button>
                    <Link to={`/cities/${city.id}/orders`} className="btn btn-sm btn-outline-info">
                      <i className="fas fa-eye"></i> الطلبات
                    </Link>
                  </td>
                </tr>
              ))}
              {cities.length === 0 && (
                <tr><td colSpan={5} className="text-center text-muted py-4">لا توجد مدن</td></tr>
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
                <h5 className="modal-title">{editCity ? 'تعديل مدينة' : 'إضافة مدينة جديدة'}</h5>
                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
              </div>
              <div className="modal-body">
                <div className="mb-3">
                  <label className="form-label">اسم المدينة</label>
                  <input
                    type="text"
                    className="form-control"
                    value={cityName}
                    onChange={e => setCityName(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && handleSave()}
                    autoFocus
                  />
                </div>
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowModal(false)}>إلغاء</button>
                <button className="btn btn-primary" onClick={handleSave}>
                  {editCity ? 'تعديل' : 'إضافة'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default CitiesPage;
