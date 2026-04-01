import { Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import HomePage from './pages/HomePage';
import CitiesPage from './pages/CitiesPage';
import CityOrdersPage from './pages/CityOrdersPage';
import DriversPage from './pages/DriversPage';
import DriverOrdersPage from './pages/DriverOrdersPage';
import DriverAttachPage from './pages/DriverAttachPage';
import ManifestsPage from './pages/ManifestsPage';
import ManifestCreatePage from './pages/ManifestCreatePage';
import ManifestOrdersPage from './pages/ManifestOrdersPage';
import ManageOrdersPage from './pages/ManageOrdersPage';
import CheckOrdersPage from './pages/CheckOrdersPage';
import SettingsPage from './pages/SettingsPage';

function App() {
  return (
    <Layout>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/cities" element={<CitiesPage />} />
        <Route path="/cities/:id/orders" element={<CityOrdersPage />} />
        <Route path="/drivers" element={<DriversPage />} />
        <Route path="/drivers/:id/orders" element={<DriverOrdersPage />} />
        <Route path="/drivers/:id/attach" element={<DriverAttachPage />} />
        <Route path="/menafests/incoming" element={<ManifestsPage type="incoming" />} />
        <Route path="/menafests/outgoing" element={<ManifestsPage type="outgoing" />} />
        <Route path="/menafests/create/:type" element={<ManifestCreatePage />} />
        <Route path="/menafests/:id/edit" element={<ManifestCreatePage />} />
        <Route path="/menafests/:id/orders" element={<ManifestOrdersPage />} />
        <Route path="/manage-orders" element={<ManageOrdersPage />} />
        <Route path="/check-orders" element={<CheckOrdersPage />} />
        <Route path="/settings" element={<SettingsPage />} />
      </Routes>
    </Layout>
  );
}

export default App;
