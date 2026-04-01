import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { City } from '../types';
import Toast from '../components/Toast';

function HomePage() {
  const [cities, setCities] = useState<City[]>([]);
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  useEffect(() => {
    loadData();
  }, []);

  async function loadData() {
    const result = await window.electronAPI.getHomeStats();
    setCities(result.cities);
  }

  async function handleBackup() {
    const result = await window.electronAPI.manualBackup();
    if (result.success) {
      setToast({ show: true, message: 'تم إنشاء نسخة احتياطية بنجاح', type: 'success' });
    } else {
      setToast({ show: true, message: 'فشل إنشاء النسخة الاحتياطية: ' + result.error, type: 'error' });
    }
  }

  const localCity = cities.find(c => c.is_local);
  const otherCities = cities.filter(c => !c.is_local);

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2><i className="fas fa-home me-2"></i> الرئيسية</h2>
        <button className="btn btn-outline-primary" onClick={handleBackup}>
          <i className="fas fa-database me-1"></i> نسخة احتياطية
        </button>
      </div>

      {!localCity && (
        <div className="alert alert-warning">
          <i className="fas fa-exclamation-triangle me-2"></i>
          الرجاء تحديد المدينة المحلية من <Link to="/settings">الإعدادات</Link> أولاً
        </div>
      )}

      {localCity && (
        <div className="card mb-4">
          <div className="card-header bg-primary text-white">
            <i className="fas fa-map-marker-alt me-2"></i> المدينة المحلية: {localCity.name}
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-6">
                <Link to="/menafests/incoming" className="btn btn-outline-success btn-lg w-100 p-3">
                  <i className="fas fa-arrow-down me-2"></i> منافست وارد
                </Link>
              </div>
              <div className="col-md-6">
                <Link to="/menafests/outgoing" className="btn btn-outline-danger btn-lg w-100 p-3">
                  <i className="fas fa-arrow-up me-2"></i> منافست صادر
                </Link>
              </div>
              <div className="col-md-6">
                <Link to="/manage-orders" className="btn btn-outline-primary btn-lg w-100 p-3">
                  <i className="fas fa-boxes-stacked me-2"></i> إدارة الطلبات
                </Link>
              </div>
              <div className="col-md-6">
                <Link to="/check-orders" className="btn btn-outline-warning btn-lg w-100 p-3">
                  <i className="fas fa-clipboard-check me-2"></i> تشطيب
                </Link>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="card">
        <div className="card-header bg-dark text-white">
          <i className="fas fa-city me-2"></i> المدن المسجلة ({cities.length})
        </div>
        <div className="card-body">
          <div className="row g-3">
            {otherCities.map(city => (
              <div key={city.id} className="col-md-3">
                <Link to={`/cities/${city.id}/orders`} className="text-decoration-none">
                  <div className="card stat-card text-center p-3">
                    <i className="fas fa-city fa-2x mb-2 text-primary"></i>
                    <h6>{city.name}</h6>
                  </div>
                </Link>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

export default HomePage;
