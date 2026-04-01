import { contextBridge, ipcRenderer } from 'electron';

contextBridge.exposeInMainWorld('electronAPI', {
  // Cities
  getCities: (page?: number, limit?: number) => ipcRenderer.invoke('cities:getAll', page, limit),
  getCity: (id: number) => ipcRenderer.invoke('cities:get', id),
  createCity: (data: { name: string }) => ipcRenderer.invoke('cities:create', data),
  updateCity: (id: number, data: { name: string }) => ipcRenderer.invoke('cities:update', id, data),
  getCityOrders: (cityId: number, filters: Record<string, unknown>) => ipcRenderer.invoke('cities:orders', cityId, filters),

  // Drivers
  getDrivers: (page?: number, limit?: number) => ipcRenderer.invoke('drivers:getAll', page, limit),
  getDriver: (id: number) => ipcRenderer.invoke('drivers:get', id),
  createDriver: (data: { name: string; notes?: string }) => ipcRenderer.invoke('drivers:create', data),
  updateDriver: (id: number, data: { name: string; notes?: string }) => ipcRenderer.invoke('drivers:update', id, data),
  getDriverOrders: (driverId: number, filters: Record<string, unknown>) => ipcRenderer.invoke('drivers:orders', driverId, filters),
  getUnassignedOrders: (filters: Record<string, unknown>) => ipcRenderer.invoke('drivers:unassignedOrders', filters),
  attachOrdersToDriver: (driverId: number, orderIds: number[]) => ipcRenderer.invoke('drivers:attachOrders', driverId, orderIds),
  detachOrderFromDriver: (driverId: number, orderId: number) => ipcRenderer.invoke('drivers:detachOrder', driverId, orderId),

  // Manifests
  getIncomingManifests: (filters: Record<string, unknown>) => ipcRenderer.invoke('menafests:incoming', filters),
  getOutgoingManifests: (filters: Record<string, unknown>) => ipcRenderer.invoke('menafests:outgoing', filters),
  getManifest: (id: number) => ipcRenderer.invoke('menafests:get', id),
  createManifest: (data: Record<string, unknown>) => ipcRenderer.invoke('menafests:create', data),
  updateManifest: (id: number, data: Record<string, unknown>) => ipcRenderer.invoke('menafests:update', id, data),
  deleteManifest: (id: number) => ipcRenderer.invoke('menafests:delete', id),
  getManifestCreateData: (type: string) => ipcRenderer.invoke('menafests:createData', type),

  // Orders
  getManifestOrders: (menafestId: number) => ipcRenderer.invoke('orders:getByManifest', menafestId),
  createOrder: (menafestId: number, data: Record<string, unknown>) => ipcRenderer.invoke('orders:create', menafestId, data),
  updateOrder: (orderId: number, data: Record<string, unknown>) => ipcRenderer.invoke('orders:update', orderId, data),
  getOrder: (orderId: number) => ipcRenderer.invoke('orders:get', orderId),
  toggleOrderPaid: (orderId: number) => ipcRenderer.invoke('orders:togglePaid', orderId),
  toggleOrderExist: (orderId: number) => ipcRenderer.invoke('orders:toggleExist', orderId),
  updateOrderNotes: (orderId: number, notes: string) => ipcRenderer.invoke('orders:updateNotes', orderId, notes),

  // Manage Orders
  getManageOrders: (filters: Record<string, unknown>) => ipcRenderer.invoke('manageOrders:getAll', filters),

  // Check Orders
  searchOrder: (number: string) => ipcRenderer.invoke('checkOrders:search', number),
  markOrderPaid: (orderId: number) => ipcRenderer.invoke('checkOrders:markPaid', orderId),
  getTodayStats: () => ipcRenderer.invoke('checkOrders:todayStats'),

  // Settings
  getSettings: () => ipcRenderer.invoke('settings:get'),
  updateLocalCity: (cityId: number) => ipcRenderer.invoke('settings:updateLocalCity', cityId),

  // Excel
  importExcelPreview: (filePath: string, menafestId: number) => ipcRenderer.invoke('excel:preview', filePath, menafestId),
  importExcelConfirm: (menafestId: number, orders: Record<string, unknown>[]) => ipcRenderer.invoke('excel:confirm', menafestId, orders),
  exportManifestExcel: (menafestId: number, savePath: string) => ipcRenderer.invoke('excel:exportManifest', menafestId, savePath),

  // Dialogs
  openFileDialog: (filters?: Array<{ name: string; extensions: string[] }>) => ipcRenderer.invoke('dialog:openFile', filters),
  saveFileDialog: (defaultName?: string, filters?: Array<{ name: string; extensions: string[] }>) => ipcRenderer.invoke('dialog:saveFile', defaultName, filters),

  // Backup
  manualBackup: () => ipcRenderer.invoke('backup:manual'),

  // Home
  getHomeStats: () => ipcRenderer.invoke('home:stats'),
});
