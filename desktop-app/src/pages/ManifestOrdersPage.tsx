import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Order, Menafest } from '../types';
import { formatNumber, formatDateTime } from '../utils/format';
import Toast from '../components/Toast';

function ManifestOrdersPage() {
  const { id } = useParams<{ id: string }>();
  const [menafest, setMenafest] = useState<(Menafest & { type: string }) | null>(null);
  const [orders, setOrders] = useState<Order[]>([]);
  const [showAddForm, setShowAddForm] = useState(false);
  const [editingOrder, setEditingOrder] = useState<Order | null>(null);
  const [toast, setToast] = useState({ show: false, message: '', type: 'success' as 'success' | 'error' | 'warning' | 'info' });

  const [orderForm, setOrderForm] = useState({
    order_number: '', content: 'طرد', count: '1', sender: '', recipient: '',
    pay_type: 'مسبق', amount: '0', anti_charger: '0', transmitted: '0',
    miscellaneous: '0', discount: '0', notes: '',
  });

  const loadData = useCallback(async () => {
    if (!id) return;
    const result = await window.electronAPI.getManifestOrders(Number(id));
    setMenafest(result.menafest);
    setOrders(result.orders);
  }, [id]);

  useEffect(() => { loadData(); }, [loadData]);

  function resetForm() {
    setOrderForm({
      order_number: '', content: 'طرد', count: '1', sender: '', recipient: '',
      pay_type: 'مسبق', amount: '0', anti_charger: '0', transmitted: '0',
      miscellaneous: '0', discount: '0', notes: '',
    });
  }

  function openAdd() {
    setEditingOrder(null);
    resetForm();
    setShowAddForm(true);
  }

  function openEdit(order: Order) {
    setEditingOrder(order);
    setOrderForm({
      order_number: order.order_number,
      content: order.content,
      count: String(order.count),
      sender: order.sender,
      recipient: order.recipient,
      pay_type: order.pay_type,
      amount: String(order.amount),
      anti_charger: String(order.anti_charger),
      transmitted: String(order.transmitted),
      miscellaneous: String(order.miscellaneous),
      discount: String(order.discount),
      notes: order.notes || '',
    });
    setShowAddForm(true);
  }

  async function handleSaveOrder() {
    if (!id || !orderForm.order_number || !orderForm.sender || !orderForm.recipient) {
      setToast({ show: true, message: 'الرجاء ملء الحقول المطلوبة', type: 'error' });
      return;
    }

    const data = {
      order_number: orderForm.order_number,
      content: orderForm.content || 'طرد',
      count: Number(orderForm.count) || 1,
      sender: orderForm.sender,
      recipient: orderForm.recipient,
      pay_type: orderForm.pay_type,
      amount: Number(orderForm.amount) || 0,
      anti_charger: Number(orderForm.anti_charger) || 0,
      transmitted: Number(orderForm.transmitted) || 0,
      miscellaneous: Number(orderForm.miscellaneous) || 0,
      discount: Number(orderForm.discount) || 0,
      notes: orderForm.notes || null,
    };

    if (editingOrder) {
      await window.electronAPI.updateOrder(editingOrder.id, data);
      setToast({ show: true, message: 'تم تعديل الطلب بنجاح', type: 'success' });
    } else {
      await window.electronAPI.createOrder(Number(id), data);
      setToast({ show: true, message: 'تم إضافة الطلب بنجاح', type: 'success' });
    }
    setShowAddForm(false);
    loadData();
  }

  async function handleTogglePaid(orderId: number) {
    await window.electronAPI.toggleOrderPaid(orderId);
    loadData();
  }

  async function handleToggleExist(orderId: number) {
    await window.electronAPI.toggleOrderExist(orderId);
    loadData();
  }

  async function handleImportExcel() {
    const result = await window.electronAPI.openFileDialog();
    if (result.canceled || result.filePaths.length === 0) return;

    const filePath = result.filePaths[0];
    const preview = await window.electronAPI.importExcelPreview(filePath, Number(id));

    if (preview.errors.length > 0) {
      setToast({ show: true, message: `تحذيرات: ${preview.errors.join(', ')}`, type: 'warning' });
    }

    if (preview.orders.length > 0) {
      const confirmResult = await window.electronAPI.importExcelConfirm(Number(id), preview.orders);
      if (confirmResult.success) {
        setToast({ show: true, message: `تم استيراد ${confirmResult.count} طلب بنجاح`, type: 'success' });
        loadData();
      }
    } else {
      setToast({ show: true, message: 'لم يتم العثور على طلبات في الملف', type: 'error' });
    }
  }

  async function handleExportExcel() {
    if (!menafest) return;
    const result = await window.electronAPI.saveFileDialog(
      `manifest_${menafest.manafest_code}.xlsx`
    );
    if (result.canceled || !result.filePath) return;

    const exportResult = await window.electronAPI.exportManifestExcel(Number(id), result.filePath);
    if (exportResult.success) {
      setToast({ show: true, message: 'تم تصدير الملف بنجاح', type: 'success' });
    } else {
      setToast({ show: true, message: 'فشل التصدير: ' + exportResult.error, type: 'error' });
    }
  }

  // Compute stats
  const totalOrders = orders.length;
  const totalItems = orders.reduce((s, o) => s + o.count, 0);
  const totalAmount = orders.reduce((s, o) => s + o.amount, 0);
  const cashOrders = orders.filter(o => o.pay_type === 'تحصيل');
  const prepaidOrders = orders.filter(o => o.pay_type === 'مسبق');

  const backPath = menafest?.type === 'outgoing' ? '/menafests/outgoing' : '/menafests/incoming';

  return (
    <div>
      <Toast {...toast} onClose={() => setToast(t => ({ ...t, show: false }))} />

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>
          <i className="fas fa-file-alt me-2"></i>
          منفست: {menafest?.manafest_code || '...'}
          <small className="text-muted ms-2">({menafest?.from_city_name} ← {menafest?.to_city_name})</small>
        </h2>
        <div>
          <button className="btn btn-success me-2" onClick={handleImportExcel}>
            <i className="fas fa-file-import me-1"></i> استيراد Excel
          </button>
          <button className="btn btn-info me-2 text-white" onClick={handleExportExcel}>
            <i className="fas fa-file-export me-1"></i> تصدير Excel
          </button>
          <button className="btn btn-primary me-2" onClick={openAdd}>
            <i className="fas fa-plus me-1"></i> إضافة طلب
          </button>
          <Link to={backPath} className="btn btn-outline-dark">
            <i className="fas fa-arrow-right me-1"></i> رجوع
          </Link>
        </div>
      </div>

      {/* Manifest Info */}
      {menafest && (
        <div className="card mb-3">
          <div className="card-body">
            <div className="row">
              <div className="col-md-3"><strong>السائق:</strong> {menafest.driver_name}</div>
              <div className="col-md-3"><strong>السيارة:</strong> {menafest.car}</div>
              <div className="col-md-3"><strong>تاريخ الإنشاء:</strong> {formatDateTime(menafest.created_at)}</div>
              <div className="col-md-3"><strong>ملاحظات:</strong> {menafest.notes || '---'}</div>
            </div>
          </div>
        </div>
      )}

      {/* Stats */}
      <div className="stats-grid">
        <div className="stat-item">
          <div className="stat-value">{totalOrders}</div>
          <div className="stat-label">إجمالي الطلبات</div>
        </div>
        <div className="stat-item">
          <div className="stat-value">{totalItems}</div>
          <div className="stat-label">إجمالي القطع</div>
        </div>
        <div className="stat-item">
          <div className="stat-value">{formatNumber(totalAmount)}</div>
          <div className="stat-label">إجمالي المبالغ</div>
        </div>
        <div className="stat-item">
          <div className="stat-value">{formatNumber(cashOrders.reduce((s, o) => s + o.amount, 0))}</div>
          <div className="stat-label">تحصيل ({cashOrders.length})</div>
        </div>
        <div className="stat-item">
          <div className="stat-value">{formatNumber(prepaidOrders.reduce((s, o) => s + o.amount, 0))}</div>
          <div className="stat-label">مسبق ({prepaidOrders.length})</div>
        </div>
      </div>

      {/* Orders Table */}
      <div className="card">
        <div className="card-body table-responsive">
          <table className="table table-hover table-sm">
            <thead>
              <tr>
                <th>#</th>
                <th>رقم الطلب</th>
                <th>المحتوى</th>
                <th>العدد</th>
                <th>المرسل</th>
                <th>المرسل إليه</th>
                <th>الدفع</th>
                <th>المبلغ</th>
                <th>ضد الشاحن</th>
                <th>المحول</th>
                <th>متفرقات</th>
                <th>خصم</th>
                <th>الحالة</th>
                <th>الوجود</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((order, idx) => (
                <tr key={order.id}>
                  <td>{idx + 1}</td>
                  <td><strong>{order.order_number}</strong></td>
                  <td>{order.content}</td>
                  <td>{order.count}</td>
                  <td>{order.sender}</td>
                  <td>{order.recipient}</td>
                  <td>
                    <span className={`badge ${order.pay_type === 'تحصيل' ? 'badge-cash' : 'badge-prepaid'}`}>
                      {order.pay_type}
                    </span>
                  </td>
                  <td>{formatNumber(order.amount)}</td>
                  <td>{formatNumber(order.anti_charger)}</td>
                  <td>{formatNumber(order.transmitted)}</td>
                  <td>{formatNumber(order.miscellaneous)}</td>
                  <td>{formatNumber(order.discount)}</td>
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
                  <td>
                    <button className="btn btn-sm btn-outline-primary" onClick={() => openEdit(order)} title="تعديل">
                      <i className="fas fa-edit"></i>
                    </button>
                  </td>
                </tr>
              ))}
              {orders.length === 0 && (
                <tr><td colSpan={15} className="text-center text-muted py-4">لا توجد طلبات</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add/Edit Order Modal */}
      {showAddForm && (
        <div className="modal d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-lg">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">{editingOrder ? 'تعديل طلب' : 'إضافة طلب جديد'}</h5>
                <button type="button" className="btn-close" onClick={() => setShowAddForm(false)}></button>
              </div>
              <div className="modal-body">
                <div className="row g-3">
                  <div className="col-md-4">
                    <label className="form-label">رقم الطلب *</label>
                    <input type="text" className="form-control" value={orderForm.order_number}
                      onChange={e => setOrderForm(p => ({ ...p, order_number: e.target.value }))} autoFocus />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">المحتوى</label>
                    <input type="text" className="form-control" value={orderForm.content}
                      onChange={e => setOrderForm(p => ({ ...p, content: e.target.value }))} />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">العدد</label>
                    <input type="number" className="form-control" value={orderForm.count}
                      onChange={e => setOrderForm(p => ({ ...p, count: e.target.value }))} />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label">المرسل *</label>
                    <input type="text" className="form-control" value={orderForm.sender}
                      onChange={e => setOrderForm(p => ({ ...p, sender: e.target.value }))} />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label">المرسل إليه *</label>
                    <input type="text" className="form-control" value={orderForm.recipient}
                      onChange={e => setOrderForm(p => ({ ...p, recipient: e.target.value }))} />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">نوع الدفع</label>
                    <select className="form-select" value={orderForm.pay_type}
                      onChange={e => setOrderForm(p => ({ ...p, pay_type: e.target.value }))}>
                      <option value="مسبق">مسبق</option>
                      <option value="تحصيل">تحصيل</option>
                    </select>
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">المبلغ</label>
                    <input type="number" className="form-control" value={orderForm.amount}
                      onChange={e => setOrderForm(p => ({ ...p, amount: e.target.value }))} />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">ضد الشاحن</label>
                    <input type="number" className="form-control" value={orderForm.anti_charger}
                      onChange={e => setOrderForm(p => ({ ...p, anti_charger: e.target.value }))} />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">المحول</label>
                    <input type="number" className="form-control" value={orderForm.transmitted}
                      onChange={e => setOrderForm(p => ({ ...p, transmitted: e.target.value }))} />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">متفرقات</label>
                    <input type="number" className="form-control" value={orderForm.miscellaneous}
                      onChange={e => setOrderForm(p => ({ ...p, miscellaneous: e.target.value }))} />
                  </div>
                  <div className="col-md-4">
                    <label className="form-label">الخصم</label>
                    <input type="number" className="form-control" value={orderForm.discount}
                      onChange={e => setOrderForm(p => ({ ...p, discount: e.target.value }))} />
                  </div>
                  <div className="col-12">
                    <label className="form-label">ملاحظات</label>
                    <textarea className="form-control" value={orderForm.notes}
                      onChange={e => setOrderForm(p => ({ ...p, notes: e.target.value }))} rows={2} />
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <button className="btn btn-secondary" onClick={() => setShowAddForm(false)}>إلغاء</button>
                <button className="btn btn-primary" onClick={handleSaveOrder}>
                  {editingOrder ? 'حفظ التعديلات' : 'إضافة الطلب'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default ManifestOrdersPage;
