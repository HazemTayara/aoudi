import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { City } from '../types';
import Toast from '../components/Toast';

function ManifestCreatePage() {
  const { type, id } = useParams<{ type?: string; id?: string }>();
  const navigate = useNavigate();
  const isEdit = !!id;
  const manifestType = type || 'incoming';

  const [fromCities, setFromCities] = useState<City[]>([]);
  const [toCities, setToCities] = useState<City[]>([]);
  const [form, setForm] = useState({
    from_city_id: '',
    to_city_id: '',
    manafest_code: '',
    driver_name: '',
    car: '',
    notes: '',
  });
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  useEffect(() => {
    loadData();
  }, [id, manifestType]);

  async function loadData() {
    if (isEdit) {
      const menafest = await window.electronAPI.getManifest(Number(id));
      if (menafest) {
        setForm({
          from_city_id: String(menafest.from_city_id),
          to_city_id: String(menafest.to_city_id),
          manafest_code: menafest.manafest_code,
          driver_name: menafest.driver_name,
          car: menafest.car,
          notes: menafest.notes || '',
        });
        // Determine type from manifest data
        const settings = await window.electronAPI.getSettings();
        const localCity = settings.localCity;
        const mType = localCity && menafest.from_city_id === localCity.id ? 'outgoing' : 'incoming';
        const createData = await window.electronAPI.getManifestCreateData(mType);
        setFromCities(createData.fromCities);
        setToCities(createData.toCities);
      }
    } else {
      const createData = await window.electronAPI.getManifestCreateData(manifestType);
      setFromCities(createData.fromCities);
      setToCities(createData.toCities);
      // Auto-select local city
      if (manifestType === 'outgoing' && createData.fromCities.length > 0) {
        setForm(prev => ({ ...prev, from_city_id: String(createData.fromCities[0].id) }));
      } else if (manifestType === 'incoming' && createData.toCities.length > 0) {
        setForm(prev => ({ ...prev, to_city_id: String(createData.toCities[0].id) }));
      }
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!form.from_city_id || !form.to_city_id || !form.manafest_code || !form.driver_name || !form.car) {
      setToast({ show: true, message: 'الرجاء ملء جميع الحقول المطلوبة', type: 'error' });
      return;
    }

    const data = {
      from_city_id: Number(form.from_city_id),
      to_city_id: Number(form.to_city_id),
      manafest_code: form.manafest_code,
      driver_name: form.driver_name,
      car: form.car,
      notes: form.notes || null,
    };

    if (isEdit) {
      await window.electronAPI.updateManifest(Number(id), data);
      setToast({ show: true, message: 'تم تعديل المنفست بنجاح', type: 'success' });
      setTimeout(() => navigate(-1), 500);
    } else {
      const result = await window.electronAPI.createManifest(data);
      setToast({ show: true, message: 'تم إنشاء المنفست بنجاح', type: 'success' });
      setTimeout(() => navigate(`/menafests/${result.id}/orders`), 500);
    }
  }

  const pageTitle = isEdit ? 'تعديل منفست' : (manifestType === 'outgoing' ? 'إنشاء منفست صادر' : 'إنشاء منفست وارد');

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2><i className="fas fa-file-alt me-2"></i> {pageTitle}</h2>
        <button className="btn btn-outline-dark" onClick={() => navigate(-1)}>
          <i className="fas fa-arrow-right me-1"></i> رجوع
        </button>
      </div>

      <div className="card">
        <div className="card-body">
          <form onSubmit={handleSubmit}>
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label">من مدينة *</label>
                <select
                  className="form-select"
                  value={form.from_city_id}
                  onChange={e => setForm(prev => ({ ...prev, from_city_id: e.target.value }))}
                  disabled={manifestType === 'outgoing' && !isEdit}
                >
                  <option value="">اختر المدينة</option>
                  {fromCities.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>
              <div className="col-md-6">
                <label className="form-label">إلى مدينة *</label>
                <select
                  className="form-select"
                  value={form.to_city_id}
                  onChange={e => setForm(prev => ({ ...prev, to_city_id: e.target.value }))}
                  disabled={manifestType === 'incoming' && !isEdit}
                >
                  <option value="">اختر المدينة</option>
                  {toCities.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>
              <div className="col-md-4">
                <label className="form-label">رقم المنفست *</label>
                <input
                  type="text"
                  className="form-control"
                  value={form.manafest_code}
                  onChange={e => setForm(prev => ({ ...prev, manafest_code: e.target.value }))}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">اسم السائق *</label>
                <input
                  type="text"
                  className="form-control"
                  value={form.driver_name}
                  onChange={e => setForm(prev => ({ ...prev, driver_name: e.target.value }))}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">السيارة *</label>
                <input
                  type="text"
                  className="form-control"
                  value={form.car}
                  onChange={e => setForm(prev => ({ ...prev, car: e.target.value }))}
                />
              </div>
              <div className="col-12">
                <label className="form-label">ملاحظات</label>
                <textarea
                  className="form-control"
                  value={form.notes}
                  onChange={e => setForm(prev => ({ ...prev, notes: e.target.value }))}
                  rows={3}
                />
              </div>
              <div className="col-12">
                <button type="submit" className="btn btn-primary btn-lg">
                  <i className={`fas ${isEdit ? 'fa-save' : 'fa-plus'} me-1`}></i>
                  {isEdit ? 'حفظ التعديلات' : 'إنشاء المنفست'}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

export default ManifestCreatePage;
