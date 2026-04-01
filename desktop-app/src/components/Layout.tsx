import { NavLink } from 'react-router-dom';

interface LayoutProps {
  children: React.ReactNode;
}

function Layout({ children }: LayoutProps) {
  return (
    <div className="d-flex">
      <nav className="sidebar">
        <div className="logo-section">
          <h5>شحن العودة</h5>
          <small className="text-white-50">لوحة الإدارة</small>
        </div>
        <ul className="nav flex-column mt-2">
          <li className="nav-item">
            <NavLink to="/" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-home"></i> الرئيسية
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/menafests/incoming" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-arrow-down"></i> منافست وارد
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/menafests/outgoing" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-arrow-up"></i> منافست صادر
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/manage-orders" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-boxes-stacked"></i> إدارة الطلبات
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/check-orders" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-clipboard-check"></i> تشطيب
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/cities" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-city"></i> المدن
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/drivers" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-truck"></i> السائقين
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/settings" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <i className="fas fa-cog"></i> الإعدادات
            </NavLink>
          </li>
        </ul>
      </nav>
      <main className="main-content flex-grow-1">
        {children}
      </main>
    </div>
  );
}

export default Layout;
