import { OrderStats } from '../types';
import { formatNumber } from '../utils/format';

interface OrderStatsDisplayProps {
  stats: OrderStats;
}

function OrderStatsDisplay({ stats }: OrderStatsDisplayProps) {
  return (
    <div className="stats-grid">
      <div className="stat-item">
        <div className="stat-value">{stats.total_count}</div>
        <div className="stat-label">إجمالي الطلبات</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{stats.total_items}</div>
        <div className="stat-label">إجمالي القطع</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_amount)}</div>
        <div className="stat-label">إجمالي المبالغ</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_cash_amount)}</div>
        <div className="stat-label">تحصيل ({stats.cash_count})</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_prepaid_amount)}</div>
        <div className="stat-label">مسبق ({stats.prepaid_count})</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_anti_charger)}</div>
        <div className="stat-label">ضد الشاحن ({stats.anti_charger_count})</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_transmitted)}</div>
        <div className="stat-label">المحول ({stats.transmitted_count})</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_miscellaneous)}</div>
        <div className="stat-label">متفرقات ({stats.miscellaneous_count})</div>
      </div>
      <div className="stat-item">
        <div className="stat-value">{formatNumber(stats.total_discount)}</div>
        <div className="stat-label">الخصم ({stats.discount_count})</div>
      </div>
      <div className="stat-item">
        <div className="stat-value text-success">{stats.paid_count}</div>
        <div className="stat-label">مدفوع</div>
      </div>
      <div className="stat-item">
        <div className="stat-value text-danger">{stats.unpaid_count}</div>
        <div className="stat-label">غير مدفوع</div>
      </div>
      <div className="stat-item">
        <div className="stat-value text-info">{stats.exist_count}</div>
        <div className="stat-label">موجود</div>
      </div>
    </div>
  );
}

export default OrderStatsDisplay;
