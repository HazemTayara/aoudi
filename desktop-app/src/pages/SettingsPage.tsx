import { useState, useEffect } from 'react';
import { City } from '../types';
import Toast from '../components/Toast';

function SettingsPage() {
  const [cities, setCities] = useState<City[]>([]);
  const [localCity, setLocalCity] = useState<City | null>(null);
  const [selectedCityId, setSelectedCityId] = useState<string>('');
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  useEffect(() => { loadSettings(); }, []);

  async function loadSettings() {
    const result = await window.electronAPI.getSettings();
    setCities(result.cities);
    setLocalCity(result.localCity);
    if (result.localCity) {
      setSelectedCityId(String(result.localCity.id));
    }
  }

  async function handleSave() {
    if (!selectedCityId) {
      setToast({ show: true, message: 'الرجاء اختيار مدينة', type: 'error' });
      return;
    }
    await window.electronAPI.updateLocalCity(Number(selectedCityId));
    setToast({ show: true, message: 'تم تحديث المدينة المحلية بنجاح', type: 'success' });
    loadSettings();
  }

  async function handleBackup() {
    const result = await window.electronAPI.manualBackup();
    if (result.success) {
      setToast({ show: true, message: 'تم إنشاء نسخة احتياطية بنجاح', type: 'success' });
    } else {
      setToast({ show: true, message: 'فشل إنشاء النسخة الاحتياطية', type: 'error' });
    }
  }

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <h2 className="mb-4"><i className="fas fa-cog me-2"></i> الإعدادات</h2>

      <div className="row g-4">
        {/* Local City Setting */}
        <div className="col-md-6">
          <div className="card">
            <div className="card-header bg-primary text-white">
              <i className="fas fa-map-marker-alt me-2"></i> المدينة المحلية
            </div>
            <div className="card-body">
              <p className="text-muted mb-3">
                حدد المدينة التي يعمل منها هذا المكتب. سيتم استخدامها لتحديد المنافست الواردة والصادرة.
              </p>
              {localCity && (
                <div className="alert alert-info">
                  <i className="fas fa-info-circle me-2"></i>
                  المدينة المحلية الحالية: <strong>{localCity.name}</strong>
                </div>
              )}
              <div className="mb-3">
                <label className="form-label">اختر المدينة المحلية</label>
                <select
                  className="form-select"
                  value={selectedCityId}
                  onChange={e => setSelectedCityId(e.target.value)}
                >
                  <option value="">-- اختر مدينة --</option>
                  {cities.map(city => (
                    <option key={city.id} value={city.id}>{city.name}</option>
                  ))}
                </select>
              </div>
              <button className="btn btn-primary" onClick={handleSave}>
                <i className="fas fa-save me-1"></i> حفظ
              </button>
            </div>
          </div>
        </div>

        {/* Backup Setting */}
        <div className="col-md-6">
          <div className="card">
            <div className="card-header bg-success text-white">
              <i className="fas fa-database me-2"></i> النسخ الاحتياطي
            </div>
            <div className="card-body">
              <p className="text-muted mb-3">
                يتم إنشاء نسخة احتياطية تلقائياً كل أسبوعين. يمكنك أيضاً إنشاء نسخة احتياطية يدوياً في أي وقت.
              </p>
              <ul className="list-unstyled mb-3">
                <li><i className="fas fa-check text-success me-2"></i> نسخ احتياطي تلقائي كل أسبوعين</li>
                <li><i className="fas fa-check text-success me-2"></i> يتم الاحتفاظ بآخر 10 نسخ</li>
                <li><i className="fas fa-check text-success me-2"></i> النسخ القديمة تُحذف تلقائياً</li>
              </ul>
              <button className="btn btn-success" onClick={handleBackup}>
                <i className="fas fa-download me-1"></i> إنشاء نسخة احتياطية الآن
              </button>
            </div>
          </div>
        </div>

        {/* App Info */}
        <div className="col-md-12">
          <div className="card">
            <div className="card-header bg-dark text-white">
              <i className="fas fa-info-circle me-2"></i> معلومات التطبيق
            </div>
            <div className="card-body">
              <div className="row">
                <div className="col-md-4">
                  <strong>اسم التطبيق:</strong> شحن العودة
                </div>
                <div className="col-md-4">
                  <strong>الإصدار:</strong> 1.0.0
                </div>
                <div className="col-md-4">
                  <strong>النوع:</strong> تطبيق سطح مكتب (يعمل بدون إنترنت)
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default SettingsPage;
