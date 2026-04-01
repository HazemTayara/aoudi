export interface City {
  id: number;
  name: string;
  is_local: number;
  created_at: string;
  updated_at: string;
}

export interface Driver {
  id: number;
  name: string;
  notes: string | null;
  orders_count?: number;
  created_at: string;
  updated_at: string;
}

export interface Menafest {
  id: number;
  from_city_id: number;
  to_city_id: number;
  manafest_code: string;
  driver_name: string;
  car: string;
  notes: string | null;
  from_city_name?: string;
  to_city_name?: string;
  orders_count?: number;
  type?: string;
  created_at: string;
  updated_at: string;
}

export interface Order {
  id: number;
  menafest_id: number;
  driver_id: number | null;
  order_number: string;
  content: string;
  count: number;
  sender: string;
  recipient: string;
  pay_type: 'مسبق' | 'تحصيل';
  amount: number;
  anti_charger: number;
  transmitted: number;
  miscellaneous: number;
  discount: number;
  is_paid: number;
  paid_at: string | null;
  is_exist: number;
  notes: string | null;
  assigned_at: string | null;
  manafest_code?: string;
  from_city_name?: string;
  to_city_name?: string;
  driver_name_rel?: string;
  created_at: string;
  updated_at: string;
}

export interface Setting {
  id: number;
  key: string;
  value: string | null;
  created_at: string;
  updated_at: string;
}

export interface OrderStats {
  total_count: number;
  total_items: number;
  total_amount: number;
  total_cash_amount: number;
  cash_count: number;
  total_prepaid_amount: number;
  prepaid_count: number;
  total_anti_charger: number;
  anti_charger_count: number;
  total_transmitted: number;
  transmitted_count: number;
  total_miscellaneous: number;
  miscellaneous_count: number;
  total_discount: number;
  discount_count: number;
  paid_count: number;
  unpaid_count: number;
  exist_count: number;
}

export interface PaginatedResult<T> {
  total: number;
  page: number;
  limit: number;
  totalPages: number;
  [key: string]: T[] | number | string | Record<string, unknown> | null | undefined;
}

export interface ElectronAPI {
  getCities: (page?: number, limit?: number) => Promise<{ cities: City[]; total: number; page: number; limit: number; totalPages: number }>;
  getCity: (id: number) => Promise<City>;
  createCity: (data: { name: string }) => Promise<{ success: boolean; id: number }>;
  updateCity: (id: number, data: { name: string }) => Promise<{ success: boolean }>;
  getCityOrders: (cityId: number, filters: Record<string, unknown>) => Promise<{
    orders: Order[]; stats: OrderStats; city: City; total: number; page: number; limit: number; totalPages: number;
  }>;

  getDrivers: (page?: number, limit?: number) => Promise<{ drivers: Driver[]; total: number; page: number; limit: number; totalPages: number }>;
  getDriver: (id: number) => Promise<Driver>;
  createDriver: (data: { name: string; notes?: string }) => Promise<{ success: boolean; id: number }>;
  updateDriver: (id: number, data: { name: string; notes?: string }) => Promise<{ success: boolean }>;
  getDriverOrders: (driverId: number, filters: Record<string, unknown>) => Promise<{
    orders: Order[]; driver: Driver; total: number; page: number; limit: number; totalPages: number;
  }>;
  getUnassignedOrders: (filters: Record<string, unknown>) => Promise<{
    orders: Order[]; total: number; page: number; limit: number; totalPages: number;
  }>;
  attachOrdersToDriver: (driverId: number, orderIds: number[]) => Promise<{ success: boolean; count: number }>;
  detachOrderFromDriver: (driverId: number, orderId: number) => Promise<{ success: boolean; message?: string }>;

  getIncomingManifests: (filters: Record<string, unknown>) => Promise<{
    menafests: Menafest[]; total: number; page: number; limit: number; totalPages: number;
    localCity: City; type: string; pageTitle: string; cityStats: Record<string, number>;
  }>;
  getOutgoingManifests: (filters: Record<string, unknown>) => Promise<{
    menafests: Menafest[]; total: number; page: number; limit: number; totalPages: number;
    localCity: City; type: string; pageTitle: string; cityStats: Record<string, number>;
  }>;
  getManifest: (id: number) => Promise<Menafest>;
  createManifest: (data: Record<string, unknown>) => Promise<{ success: boolean; id: number }>;
  updateManifest: (id: number, data: Record<string, unknown>) => Promise<{ success: boolean }>;
  deleteManifest: (id: number) => Promise<{ success: boolean }>;
  getManifestCreateData: (type: string) => Promise<{ localCity: City; fromCities: City[]; toCities: City[] }>;

  getManifestOrders: (menafestId: number) => Promise<{ menafest: Menafest & { type: string }; orders: Order[] }>;
  createOrder: (menafestId: number, data: Record<string, unknown>) => Promise<{ success: boolean; order: Order }>;
  updateOrder: (orderId: number, data: Record<string, unknown>) => Promise<{ success: boolean }>;
  getOrder: (orderId: number) => Promise<Order>;
  toggleOrderPaid: (orderId: number) => Promise<{ success: boolean; is_paid: number; paid_at: string | null }>;
  toggleOrderExist: (orderId: number) => Promise<{ success: boolean; is_exist: number }>;
  updateOrderNotes: (orderId: number, notes: string) => Promise<{ success: boolean; notes: string }>;

  getManageOrders: (filters: Record<string, unknown>) => Promise<{
    orders: Order[]; stats: OrderStats; total: number; page: number; limit: number; totalPages: number;
  }>;

  searchOrder: (number: string) => Promise<{ success: boolean; order?: Order; message?: string }>;
  markOrderPaid: (orderId: number) => Promise<{ success: boolean; message: string }>;
  getTodayStats: () => Promise<{ total: number; paid: number; remaining: number }>;

  getSettings: () => Promise<{ cities: City[]; localCity: City | null }>;
  updateLocalCity: (cityId: number) => Promise<{ success: boolean }>;

  importExcelPreview: (filePath: string, menafestId: number) => Promise<{ success: boolean; orders: Record<string, unknown>[]; errors: string[] }>;
  importExcelConfirm: (menafestId: number, orders: Record<string, unknown>[]) => Promise<{ success: boolean; count: number }>;
  exportManifestExcel: (menafestId: number, savePath: string) => Promise<{ success: boolean; error?: string }>;

  openFileDialog: (filters?: Array<{ name: string; extensions: string[] }>) => Promise<{ canceled: boolean; filePaths: string[] }>;
  saveFileDialog: (defaultName?: string, filters?: Array<{ name: string; extensions: string[] }>) => Promise<{ canceled: boolean; filePath?: string }>;

  manualBackup: () => Promise<{ success: boolean; path?: string; error?: string }>;

  getHomeStats: () => Promise<{ cities: City[] }>;
}

declare global {
  interface Window {
    electronAPI: ElectronAPI;
  }
}
