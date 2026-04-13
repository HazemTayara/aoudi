import { ipcMain } from 'electron';
import { getDb } from './database';
import * as XLSX from 'xlsx';
import * as fs from 'fs';

// Helper: format number like Laravel's format_number
function formatNumber(num: number | null | undefined): string {
  if (num === null || num === undefined) return '0';
  const formatted = Number(num).toFixed(2);
  if (formatted.endsWith('.00')) {
    return formatted.slice(0, -3).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }
  return formatted.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function now(): string {
  return new Date().toISOString().replace('T', ' ').substring(0, 19);
}

function getLocalCity(): { id: number; name: string; is_local: number } | undefined {
  const db = getDb();
  return db.prepare('SELECT * FROM cities WHERE is_local = 1').get() as { id: number; name: string; is_local: number } | undefined;
}

export function registerIpcHandlers(): void {
  const db = getDb();

  // ==================== HOME ====================
  ipcMain.handle('home:stats', () => {
    const cities = db.prepare('SELECT * FROM cities').all();
    return { cities };
  });

  // ==================== CITIES ====================
  ipcMain.handle('cities:getAll', (_event, page = 1, limit = 10) => {
    const offset = (page - 1) * limit;
    const cities = db.prepare('SELECT * FROM cities ORDER BY id DESC LIMIT ? OFFSET ?').all(limit, offset);
    const total = (db.prepare('SELECT COUNT(*) as count FROM cities').get() as { count: number }).count;
    return { cities, total, page, limit, totalPages: Math.ceil(total / limit) };
  });

  ipcMain.handle('cities:get', (_event, id: number) => {
    return db.prepare('SELECT * FROM cities WHERE id = ?').get(id);
  });

  ipcMain.handle('cities:create', (_event, data: { name: string }) => {
    const stmt = db.prepare('INSERT INTO cities (name, created_at, updated_at) VALUES (?, ?, ?)');
    const ts = now();
    const result = stmt.run(data.name, ts, ts);
    return { success: true, id: result.lastInsertRowid };
  });

  ipcMain.handle('cities:update', (_event, id: number, data: { name: string }) => {
    db.prepare('UPDATE cities SET name = ?, updated_at = ? WHERE id = ?').run(data.name, now(), id);
    return { success: true };
  });

  ipcMain.handle('cities:orders', (_event, cityId: number, filters: Record<string, unknown>) => {
    const city = db.prepare('SELECT * FROM cities WHERE id = ?').get(cityId) as { id: number; name: string };
    if (!city) return { orders: [], stats: {}, city: null };

    let query = `
      SELECT o.*, m.manafest_code, m.from_city_id, m.to_city_id,
             fc.name as from_city_name, tc.name as to_city_name,
             d.name as driver_name_rel
      FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      LEFT JOIN drivers d ON o.driver_id = d.id
      WHERE fc.name = ?
    `;
    const params: unknown[] = [city.name];
    const localCity = getLocalCity();
    if (localCity) {
      query += ' AND m.from_city_id != ?';
      params.push(localCity.id);
    }

    // Apply filters
    if (filters.order_number) { query += ' AND o.order_number LIKE ?'; params.push(`%${filters.order_number}%`); }
    if (filters.content) { query += ' AND o.content LIKE ?'; params.push(`%${filters.content}%`); }
    if (filters.count) { query += ' AND o.count = ?'; params.push(filters.count); }
    if (filters.sender) { query += ' AND o.sender LIKE ?'; params.push(`%${filters.sender}%`); }
    if (filters.recipient) { query += ' AND o.recipient LIKE ?'; params.push(`%${filters.recipient}%`); }
    if (filters.menafest_code) { query += ' AND m.manafest_code LIKE ?'; params.push(`%${filters.menafest_code}%`); }
    if (filters.driver_name) { query += ' AND d.name LIKE ?'; params.push(`%${filters.driver_name}%`); }
    if (filters.notes) { query += ' AND o.notes LIKE ?'; params.push(`%${filters.notes}%`); }
    if (filters.pay_type) { query += ' AND o.pay_type = ?'; params.push(filters.pay_type); }
    if (filters.is_paid !== undefined && filters.is_paid !== '' && filters.is_paid !== null) { query += ' AND o.is_paid = ?'; params.push(Number(filters.is_paid)); }
    if (filters.is_exist !== undefined && filters.is_exist !== '' && filters.is_exist !== null) { query += ' AND o.is_exist = ?'; params.push(Number(filters.is_exist)); }

    const rangeFields = ['amount', 'anti_charger', 'transmitted', 'miscellaneous', 'discount'];
    for (const field of rangeFields) {
      if (filters[`${field}_min`]) { query += ` AND o.${field} >= ?`; params.push(filters[`${field}_min`]); }
      if (filters[`${field}_max`]) { query += ` AND o.${field} <= ?`; params.push(filters[`${field}_max`]); }
    }

    if (filters.paid_from) { query += ' AND date(o.paid_at) >= ?'; params.push(filters.paid_from); }
    if (filters.paid_to) { query += ' AND date(o.paid_at) <= ?'; params.push(filters.paid_to); }
    if (filters.created_from) { query += ' AND date(o.created_at) >= ?'; params.push(filters.created_from); }
    if (filters.created_to) { query += ' AND date(o.created_at) <= ?'; params.push(filters.created_to); }

    // Get all matching orders for stats
    const allOrders = db.prepare(query + ' ORDER BY o.created_at DESC').all(...params) as Array<Record<string, unknown>>;

    // Compute stats
    const stats = computeOrderStats(allOrders);

    // Pagination
    const page = Number(filters.page) || 1;
    const limit = Number(filters.limit) || 25;
    const offset = (page - 1) * limit;
    const orders = allOrders.slice(offset, offset + limit);
    const total = allOrders.length;

    return {
      orders,
      stats,
      city,
      total,
      page,
      limit,
      totalPages: Math.ceil(total / limit),
    };
  });

  // ==================== DRIVERS ====================
  ipcMain.handle('drivers:getAll', (_event, page = 1, limit = 10) => {
    const offset = (page - 1) * limit;
    const drivers = db.prepare(`
      SELECT d.*, (SELECT COUNT(*) FROM orders WHERE driver_id = d.id) as orders_count
      FROM drivers d ORDER BY d.id DESC LIMIT ? OFFSET ?
    `).all(limit, offset);
    const total = (db.prepare('SELECT COUNT(*) as count FROM drivers').get() as { count: number }).count;
    return { drivers, total, page, limit, totalPages: Math.ceil(total / limit) };
  });

  ipcMain.handle('drivers:get', (_event, id: number) => {
    return db.prepare('SELECT * FROM drivers WHERE id = ?').get(id);
  });

  ipcMain.handle('drivers:create', (_event, data: { name: string; notes?: string }) => {
    const ts = now();
    const result = db.prepare('INSERT INTO drivers (name, notes, created_at, updated_at) VALUES (?, ?, ?, ?)').run(data.name, data.notes || null, ts, ts);
    return { success: true, id: result.lastInsertRowid };
  });

  ipcMain.handle('drivers:update', (_event, id: number, data: { name: string; notes?: string }) => {
    db.prepare('UPDATE drivers SET name = ?, notes = ?, updated_at = ? WHERE id = ?').run(data.name, data.notes || null, now(), id);
    return { success: true };
  });

  ipcMain.handle('drivers:orders', (_event, driverId: number, filters: Record<string, unknown>) => {
    const driver = db.prepare('SELECT * FROM drivers WHERE id = ?').get(driverId) as { id: number; name: string };
    if (!driver) return { orders: [], driver: null };

    let query = `
      SELECT o.*, m.manafest_code, fc.name as from_city_name, tc.name as to_city_name
      FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      WHERE o.driver_id = ?
    `;
    const params: unknown[] = [driverId];

    if (filters.assigned_from) { query += ' AND date(o.assigned_at) >= ?'; params.push(filters.assigned_from); }
    if (filters.assigned_to) { query += ' AND date(o.assigned_at) <= ?'; params.push(filters.assigned_to); }
    if (filters.created_from) { query += ' AND date(o.created_at) >= ?'; params.push(filters.created_from); }
    if (filters.created_to) { query += ' AND date(o.created_at) <= ?'; params.push(filters.created_to); }
    if (filters.paid_from) { query += ' AND date(o.paid_at) >= ?'; params.push(filters.paid_from); }
    if (filters.paid_to) { query += ' AND date(o.paid_at) <= ?'; params.push(filters.paid_to); }
    if (filters.is_paid !== undefined && filters.is_paid !== '' && filters.is_paid !== null) { query += ' AND o.is_paid = ?'; params.push(Number(filters.is_paid)); }
    if (filters.is_exist !== undefined && filters.is_exist !== '' && filters.is_exist !== null) { query += ' AND o.is_exist = ?'; params.push(Number(filters.is_exist)); }
    if (filters.pay_type) { query += ' AND o.pay_type = ?'; params.push(filters.pay_type); }
    if (filters.order_number) { query += ' AND o.order_number LIKE ?'; params.push(`%${filters.order_number}%`); }
    if (filters.sender) { query += ' AND o.sender LIKE ?'; params.push(`%${filters.sender}%`); }
    if (filters.recipient) { query += ' AND o.recipient LIKE ?'; params.push(`%${filters.recipient}%`); }

    query += ' ORDER BY o.assigned_at DESC';

    const page = Number(filters.page) || 1;
    const limit = Number(filters.limit) || 50;
    const offset = (page - 1) * limit;

    const allOrders = db.prepare(query).all(...params);
    const orders = allOrders.slice(offset, offset + limit);

    return {
      orders,
      driver,
      total: allOrders.length,
      page,
      limit,
      totalPages: Math.ceil(allOrders.length / limit),
    };
  });

  ipcMain.handle('drivers:unassignedOrders', (_event, filters: Record<string, unknown>) => {
    const localCity = getLocalCity();
    if (!localCity) return { orders: [], total: 0 };

    let query = `
      SELECT o.*, m.manafest_code, fc.name as from_city_name, tc.name as to_city_name
      FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      WHERE o.driver_id IS NULL
        AND o.is_paid = 0
        AND o.is_exist = 1
        AND m.from_city_id != ?
    `;
    const params: unknown[] = [localCity.id];

    if (filters.order_number) { query += ' AND o.order_number LIKE ?'; params.push(`%${filters.order_number}%`); }
    if (filters.sender) { query += ' AND o.sender LIKE ?'; params.push(`%${filters.sender}%`); }
    if (filters.recipient) { query += ' AND o.recipient LIKE ?'; params.push(`%${filters.recipient}%`); }
    if (filters.pay_type) { query += ' AND o.pay_type = ?'; params.push(filters.pay_type); }

    query += ' ORDER BY o.created_at DESC';

    const page = Number(filters.page) || 1;
    const limit = Number(filters.limit) || 50;
    const offset = (page - 1) * limit;

    const allOrders = db.prepare(query).all(...params);
    const orders = allOrders.slice(offset, offset + limit);

    return {
      orders,
      total: allOrders.length,
      page,
      limit,
      totalPages: Math.ceil(allOrders.length / limit),
    };
  });

  ipcMain.handle('drivers:attachOrders', (_event, driverId: number, orderIds: number[]) => {
    const ts = now();
    const stmt = db.prepare('UPDATE orders SET driver_id = ?, assigned_at = ?, updated_at = ? WHERE id = ? AND driver_id IS NULL');
    const transaction = db.transaction(() => {
      for (const id of orderIds) {
        stmt.run(driverId, ts, ts, id);
      }
    });
    transaction();
    return { success: true, count: orderIds.length };
  });

  ipcMain.handle('drivers:detachOrder', (_event, driverId: number, orderId: number) => {
    const order = db.prepare('SELECT * FROM orders WHERE id = ? AND driver_id = ?').get(orderId, driverId);
    if (!order) return { success: false, message: 'هذا الطلب لا ينتمي لهذا السائق' };
    db.prepare('UPDATE orders SET driver_id = NULL, assigned_at = NULL, updated_at = ? WHERE id = ?').run(now(), orderId);
    return { success: true };
  });

  // ==================== MANIFESTS ====================
  ipcMain.handle('menafests:incoming', (_event, filters: Record<string, unknown>) => {
    const localCity = getLocalCity();
    if (!localCity) return { menafests: [], localCity: null, cityStats: {} };

    let query = `
      SELECT m.*, fc.name as from_city_name, tc.name as to_city_name,
             (SELECT COUNT(*) FROM orders WHERE menafest_id = m.id) as orders_count
      FROM menafests m
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      WHERE m.to_city_id = ?
    `;
    const params: unknown[] = [localCity.id];
    applyManifestFilters(query, params, filters, 'incoming');
    query = params.length > 1 ? rebuildQuery(query, params, filters, 'incoming', localCity.id) : query;

    // Rebuild with filters
    const result = buildManifestQuery(localCity.id, filters, 'incoming');

    const page = Number(filters.page) || 1;
    const limit = Number(filters.limit) || 10;
    const offset = (page - 1) * limit;
    const all = result.all;
    const menafests = all.slice(offset, offset + limit);

    // City stats for incoming (group by from_city)
    const cityStats = db.prepare(`
      SELECT fc.name, COUNT(*) as total
      FROM menafests m
      JOIN cities fc ON m.from_city_id = fc.id
      WHERE m.to_city_id = ?
      GROUP BY fc.name
    `).all(localCity.id) as Array<{ name: string; total: number }>;

    const cityStatsMap: Record<string, number> = {};
    for (const stat of cityStats) {
      cityStatsMap[stat.name] = stat.total;
    }

    return {
      menafests,
      total: all.length,
      page,
      limit,
      totalPages: Math.ceil(all.length / limit),
      localCity,
      type: 'incoming',
      pageTitle: 'منافست وارد',
      cityStats: cityStatsMap,
    };
  });

  ipcMain.handle('menafests:outgoing', (_event, filters: Record<string, unknown>) => {
    const localCity = getLocalCity();
    if (!localCity) return { menafests: [], localCity: null, cityStats: {} };

    const result = buildManifestQuery(localCity.id, filters, 'outgoing');
    const page = Number(filters.page) || 1;
    const limit = Number(filters.limit) || 10;
    const offset = (page - 1) * limit;
    const all = result.all;
    const menafests = all.slice(offset, offset + limit);

    // City stats for outgoing (group by to_city)
    const cityStats = db.prepare(`
      SELECT tc.name, COUNT(*) as total
      FROM menafests m
      JOIN cities tc ON m.to_city_id = tc.id
      WHERE m.from_city_id = ?
      GROUP BY tc.name
    `).all(localCity.id) as Array<{ name: string; total: number }>;

    const cityStatsMap: Record<string, number> = {};
    for (const stat of cityStats) {
      cityStatsMap[stat.name] = stat.total;
    }

    return {
      menafests,
      total: all.length,
      page,
      limit,
      totalPages: Math.ceil(all.length / limit),
      localCity,
      type: 'outgoing',
      pageTitle: 'منافست صادر',
      cityStats: cityStatsMap,
    };
  });

  ipcMain.handle('menafests:get', (_event, id: number) => {
    const menafest = db.prepare(`
      SELECT m.*, fc.name as from_city_name, tc.name as to_city_name
      FROM menafests m
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      WHERE m.id = ?
    `).get(id);
    return menafest;
  });

  ipcMain.handle('menafests:createData', (_event, type: string) => {
    const localCity = getLocalCity();
    if (!localCity) return { localCity: null, fromCities: [], toCities: [] };

    let fromCities, toCities;
    if (type === 'outgoing') {
      fromCities = db.prepare('SELECT * FROM cities WHERE id = ?').all(localCity.id);
      toCities = db.prepare('SELECT * FROM cities WHERE is_local = 0').all();
    } else {
      fromCities = db.prepare('SELECT * FROM cities WHERE is_local = 0').all();
      toCities = db.prepare('SELECT * FROM cities WHERE id = ?').all(localCity.id);
    }
    return { localCity, fromCities, toCities };
  });

  ipcMain.handle('menafests:create', (_event, data: Record<string, unknown>) => {
    const ts = now();
    const result = db.prepare(`
      INSERT INTO menafests (from_city_id, to_city_id, manafest_code, driver_name, car, notes, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `).run(data.from_city_id, data.to_city_id, data.manafest_code, data.driver_name, data.car, data.notes || null, ts, ts);
    return { success: true, id: result.lastInsertRowid };
  });

  ipcMain.handle('menafests:update', (_event, id: number, data: Record<string, unknown>) => {
    db.prepare(`
      UPDATE menafests SET from_city_id = ?, to_city_id = ?, manafest_code = ?, driver_name = ?, car = ?, notes = ?, updated_at = ?
      WHERE id = ?
    `).run(data.from_city_id, data.to_city_id, data.manafest_code, data.driver_name, data.car, data.notes || null, now(), id);
    return { success: true };
  });

  ipcMain.handle('menafests:delete', (_event, id: number) => {
    db.prepare('DELETE FROM menafests WHERE id = ?').run(id);
    return { success: true };
  });

  // ==================== ORDERS ====================
  ipcMain.handle('orders:getByManifest', (_event, menafestId: number) => {
    const menafest = db.prepare(`
      SELECT m.*, fc.name as from_city_name, tc.name as to_city_name
      FROM menafests m
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      WHERE m.id = ?
    `).get(menafestId) as Record<string, unknown> | undefined;

    if (!menafest) return { menafest: null, orders: [] };

    const localCity = getLocalCity();
    const type = localCity && menafest.from_city_id === localCity.id ? 'outgoing' : 'incoming';

    const orders = db.prepare('SELECT * FROM orders WHERE menafest_id = ? ORDER BY created_at DESC').all(menafestId);
    return { menafest: { ...menafest, type }, orders };
  });

  ipcMain.handle('orders:create', (_event, menafestId: number, data: Record<string, unknown>) => {
    const ts = now();
    const result = db.prepare(`
      INSERT INTO orders (menafest_id, order_number, content, count, sender, recipient, pay_type, amount, anti_charger, transmitted, miscellaneous, discount, notes, is_paid, is_exist, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?)
    `).run(
      menafestId,
      data.order_number,
      data.content || 'طرد',
      data.count || 1,
      data.sender,
      data.recipient,
      data.pay_type,
      data.amount || 0,
      data.anti_charger || 0,
      data.transmitted || 0,
      data.miscellaneous || 0,
      data.discount || 0,
      data.notes || null,
      ts, ts
    );
    const order = db.prepare('SELECT * FROM orders WHERE id = ?').get(result.lastInsertRowid);
    return { success: true, order };
  });

  ipcMain.handle('orders:get', (_event, orderId: number) => {
    return db.prepare('SELECT * FROM orders WHERE id = ?').get(orderId);
  });

  ipcMain.handle('orders:update', (_event, orderId: number, data: Record<string, unknown>) => {
    db.prepare(`
      UPDATE orders SET order_number = ?, content = ?, count = ?, sender = ?, recipient = ?, pay_type = ?,
        amount = ?, anti_charger = ?, transmitted = ?, miscellaneous = ?, discount = ?, notes = ?, updated_at = ?
      WHERE id = ?
    `).run(
      data.order_number,
      data.content || 'طرد',
      data.count || 1,
      data.sender,
      data.recipient,
      data.pay_type,
      data.amount || 0,
      data.anti_charger || 0,
      data.transmitted || 0,
      data.miscellaneous || 0,
      data.discount || 0,
      data.notes || null,
      now(),
      orderId
    );
    return { success: true };
  });

  ipcMain.handle('orders:togglePaid', (_event, orderId: number) => {
    const order = db.prepare('SELECT * FROM orders WHERE id = ?').get(orderId) as { is_paid: number } | undefined;
    if (!order) return { success: false };
    const newPaid = order.is_paid ? 0 : 1;
    const paidAt = newPaid ? now() : null;
    db.prepare('UPDATE orders SET is_paid = ?, paid_at = ?, updated_at = ? WHERE id = ?').run(newPaid, paidAt, now(), orderId);
    return { success: true, is_paid: newPaid, paid_at: paidAt };
  });

  ipcMain.handle('orders:toggleExist', (_event, orderId: number) => {
    const order = db.prepare('SELECT * FROM orders WHERE id = ?').get(orderId) as { is_exist: number } | undefined;
    if (!order) return { success: false };
    const newExist = order.is_exist ? 0 : 1;
    db.prepare('UPDATE orders SET is_exist = ?, updated_at = ? WHERE id = ?').run(newExist, now(), orderId);
    return { success: true, is_exist: newExist };
  });

  ipcMain.handle('orders:updateNotes', (_event, orderId: number, notes: string) => {
    db.prepare('UPDATE orders SET notes = ?, updated_at = ? WHERE id = ?').run(notes || null, now(), orderId);
    return { success: true, notes };
  });

  // ==================== MANAGE ORDERS ====================
  ipcMain.handle('manageOrders:getAll', (_event, filters: Record<string, unknown>) => {
    const localCity = getLocalCity();
    if (!localCity) return { orders: [], stats: {} };

    let query = `
      SELECT o.*, m.manafest_code, fc.name as from_city_name, tc.name as to_city_name, d.name as driver_name_rel
      FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      LEFT JOIN drivers d ON o.driver_id = d.id
      WHERE m.from_city_id != ?
    `;
    const params: unknown[] = [localCity.id];

    if (filters.order_number) { query += ' AND o.order_number LIKE ?'; params.push(`%${filters.order_number}%`); }
    if (filters.content) { query += ' AND o.content LIKE ?'; params.push(`%${filters.content}%`); }
    if (filters.count) { query += ' AND o.count = ?'; params.push(filters.count); }
    if (filters.sender) { query += ' AND o.sender LIKE ?'; params.push(`%${filters.sender}%`); }
    if (filters.recipient) { query += ' AND o.recipient LIKE ?'; params.push(`%${filters.recipient}%`); }
    if (filters.menafest_code) { query += ' AND m.manafest_code LIKE ?'; params.push(`%${filters.menafest_code}%`); }
    if (filters.driver_name) { query += ' AND d.name LIKE ?'; params.push(`%${filters.driver_name}%`); }
    if (filters.notes) { query += ' AND o.notes LIKE ?'; params.push(`%${filters.notes}%`); }
    if (filters.pay_type) { query += ' AND o.pay_type = ?'; params.push(filters.pay_type); }
    if (filters.is_paid !== undefined && filters.is_paid !== '' && filters.is_paid !== null) { query += ' AND o.is_paid = ?'; params.push(Number(filters.is_paid)); }
    if (filters.is_exist !== undefined && filters.is_exist !== '' && filters.is_exist !== null) { query += ' AND o.is_exist = ?'; params.push(Number(filters.is_exist)); }

    const rangeFields = ['amount', 'anti_charger', 'transmitted', 'miscellaneous', 'discount'];
    for (const field of rangeFields) {
      if (filters[`${field}_min`]) { query += ` AND o.${field} >= ?`; params.push(filters[`${field}_min`]); }
      if (filters[`${field}_max`]) { query += ` AND o.${field} <= ?`; params.push(filters[`${field}_max`]); }
    }
    if (filters.paid_from) { query += ' AND date(o.paid_at) >= ?'; params.push(filters.paid_from); }
    if (filters.paid_to) { query += ' AND date(o.paid_at) <= ?'; params.push(filters.paid_to); }
    if (filters.created_from) { query += ' AND date(o.created_at) >= ?'; params.push(filters.created_from); }
    if (filters.created_to) { query += ' AND date(o.created_at) <= ?'; params.push(filters.created_to); }

    query += ' ORDER BY o.created_at DESC';

    const allOrders = db.prepare(query).all(...params) as Array<Record<string, unknown>>;
    const stats = computeOrderStats(allOrders);

    const page = Number(filters.page) || 1;
    const limit = Number(filters.limit) || 25;
    const offset = (page - 1) * limit;
    const orders = allOrders.slice(offset, offset + limit);

    return {
      orders,
      stats,
      total: allOrders.length,
      page,
      limit,
      totalPages: Math.ceil(allOrders.length / limit),
    };
  });

  // ==================== CHECK ORDERS ====================
  ipcMain.handle('checkOrders:search', (_event, number: string) => {
    const localCity = getLocalCity();
    if (!localCity) return { success: false, message: 'الرجاء تحديد المدينة المحلية أولاً' };

    const fourteenDaysAgo = new Date();
    fourteenDaysAgo.setDate(fourteenDaysAgo.getDate() - 14);
    const dateStr = fourteenDaysAgo.toISOString().substring(0, 10);

    const order = db.prepare(`
      SELECT o.*, m.manafest_code, fc.name as from_city_name, tc.name as to_city_name, d.name as driver_name_rel
      FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      JOIN cities fc ON m.from_city_id = fc.id
      JOIN cities tc ON m.to_city_id = tc.id
      LEFT JOIN drivers d ON o.driver_id = d.id
      WHERE o.order_number LIKE ?
        AND date(o.created_at) >= ?
        AND m.to_city_id = ?
      LIMIT 1
    `).get(`%${number}%`, dateStr, localCity.id);

    if (!order) return { success: false, message: 'لم يتم العثور على طلب وارد بهذا الرقم' };
    return { success: true, order };
  });

  ipcMain.handle('checkOrders:markPaid', (_event, orderId: number) => {
    const localCity = getLocalCity();
    if (!localCity) return { success: false, message: 'الرجاء تحديد المدينة المحلية أولاً' };

    const order = db.prepare(`
      SELECT o.* FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      WHERE o.id = ? AND m.to_city_id = ?
    `).get(orderId, localCity.id) as { is_paid: number } | undefined;

    if (!order) return { success: false, message: 'لا يمكن تحديث هذا الطلب (ليس طلب وارد)' };
    if (order.is_paid) return { success: false, message: 'الطلب مدفوع بالفعل' };

    db.prepare('UPDATE orders SET is_paid = 1, paid_at = ?, updated_at = ? WHERE id = ?').run(now(), now(), orderId);
    return { success: true, message: 'تم تحديث حالة الدفع بنجاح' };
  });

  ipcMain.handle('checkOrders:todayStats', () => {
    const localCity = getLocalCity();
    if (!localCity) return { total: 0, paid: 0, remaining: 0 };

    const today = new Date().toISOString().substring(0, 10);
    const total = (db.prepare(`
      SELECT COUNT(*) as count FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      WHERE m.to_city_id = ? AND date(o.created_at) = ?
    `).get(localCity.id, today) as { count: number }).count;

    const paid = (db.prepare(`
      SELECT COUNT(*) as count FROM orders o
      JOIN menafests m ON o.menafest_id = m.id
      WHERE m.to_city_id = ? AND date(o.paid_at) = ?
    `).get(localCity.id, today) as { count: number }).count;

    return { total, paid, remaining: total - paid };
  });

  // ==================== SETTINGS ====================
  ipcMain.handle('settings:get', () => {
    const cities = db.prepare('SELECT * FROM cities').all();
    const localCity = getLocalCity();
    return { cities, localCity };
  });

  ipcMain.handle('settings:updateLocalCity', (_event, cityId: number) => {
    db.prepare('UPDATE cities SET is_local = 0').run();
    db.prepare('UPDATE cities SET is_local = 1, updated_at = ? WHERE id = ?').run(now(), cityId);
    return { success: true };
  });

  // ==================== EXCEL ====================
  ipcMain.handle('excel:preview', (_event, filePath: string, menafestId: number) => {
    try {
      const menafest = db.prepare(`
        SELECT m.*, fc.name as from_city_name FROM menafests m
        JOIN cities fc ON m.from_city_id = fc.id
        WHERE m.id = ?
      `).get(menafestId) as { from_city_name: string } | undefined;

      if (!menafest) return { success: false, orders: [], errors: ['المنفست غير موجود'] };

      const fileBuffer = fs.readFileSync(filePath);
      const workbook = XLSX.read(fileBuffer, { type: 'buffer' });
      const sheet = workbook.Sheets[workbook.SheetNames[0]];
      const rows = XLSX.utils.sheet_to_json(sheet) as Array<Record<string, unknown>>;

      const cityName = menafest.from_city_name;
      if (cityName === 'حلب') {
        return parseHalab(rows);
      }
      return parseDamascus(rows);
    } catch (error) {
      return { success: false, orders: [], errors: [String(error)] };
    }
  });

  ipcMain.handle('excel:confirm', (_event, menafestId: number, orders: Array<Record<string, unknown>>) => {
    const ts = now();
    const stmt = db.prepare(`
      INSERT INTO orders (menafest_id, order_number, content, count, sender, recipient, pay_type, amount,
        anti_charger, transmitted, miscellaneous, discount, is_paid, is_exist, notes, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const transaction = db.transaction(() => {
      for (const o of orders) {
        stmt.run(
          menafestId, o.order_number, o.content || 'طرد', o.count || 1,
          o.sender, o.recipient, o.pay_type, o.amount || 0,
          o.anti_charger || 0, o.transmitted || 0, o.miscellaneous || 0,
          o.discount || 0, o.is_paid ? 1 : 0, o.is_exist ? 1 : 0,
          o.notes || null, ts, ts
        );
      }
    });
    transaction();
    return { success: true, count: orders.length };
  });

  ipcMain.handle('excel:exportManifest', (_event, menafestId: number, savePath: string) => {
    try {
      const menafest = db.prepare(`
        SELECT m.*, fc.name as from_city_name, tc.name as to_city_name
        FROM menafests m
        JOIN cities fc ON m.from_city_id = fc.id
        JOIN cities tc ON m.to_city_id = tc.id
        WHERE m.id = ?
      `).get(menafestId) as Record<string, unknown>;

      if (!menafest) return { success: false, error: 'المنفست غير موجود' };

      const orders = db.prepare('SELECT * FROM orders WHERE menafest_id = ?').all(menafestId) as Array<Record<string, unknown>>;

      const wb = XLSX.utils.book_new();
      const wsData: unknown[][] = [];

      // Header rows
      wsData.push([`منفست - ${menafest.manafest_code}`]);
      wsData.push([`من مدينة: ${menafest.from_city_name} | إلى مدينة: ${menafest.to_city_name}`]);
      wsData.push([`السائق: ${menafest.driver_name} | السيارة: ${menafest.car} | تاريخ الإنشاء: ${new Date().toLocaleDateString('en-CA')}`]);
      wsData.push([`ملاحظات: ${menafest.notes || '---'}`]);
      wsData.push([]); // Empty row
      wsData.push(['#', 'رقم الطلب', 'المحتوى', 'العدد', 'المرسل', 'المرسل إليه', 'نوع الدفع', 'المبلغ', 'ضد الدفع', 'محول', 'متفرقات متنوعة', 'الخصم']);

      // Data rows
      let idx = 1;
      for (const order of orders) {
        wsData.push([
          idx++,
          order.order_number,
          order.content,
          order.count,
          order.sender,
          order.recipient,
          order.pay_type,
          formatNumber(order.amount as number),
          formatNumber(order.anti_charger as number),
          formatNumber(order.transmitted as number),
          formatNumber(order.miscellaneous as number),
          formatNumber(order.discount as number),
        ]);
      }

      // Stats section
      wsData.push([]);
      wsData.push(['إحصائيات المنفست']);
      wsData.push([]);
      wsData.push(['إجمالي عدد الطلبات:', orders.length]);
      wsData.push(['إجمالي عدد القطع:', orders.reduce((s, o) => s + Number(o.count), 0)]);
      wsData.push([]);
      wsData.push(['إحصائيات نوع الدفع']);
      wsData.push(['النوع', 'عدد الطلبات', 'الإجمالي']);
      const collectionOrders = orders.filter(o => o.pay_type === 'تحصيل');
      const prepaidOrders = orders.filter(o => o.pay_type === 'مسبق');
      wsData.push(['تحصيل', collectionOrders.length, formatNumber(collectionOrders.reduce((s, o) => s + Number(o.amount), 0))]);
      wsData.push(['مسبق', prepaidOrders.length, formatNumber(prepaidOrders.reduce((s, o) => s + Number(o.amount), 0))]);
      wsData.push(['إجمالي المبالغ', '', formatNumber(orders.reduce((s, o) => s + Number(o.amount), 0))]);

      const ws = XLSX.utils.aoa_to_sheet(wsData);

      // Set RTL
      if (!ws['!sheetViews']) ws['!sheetViews'] = [{}];

      // Merge header cells
      ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 11 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 11 } },
        { s: { r: 2, c: 0 }, e: { r: 2, c: 11 } },
        { s: { r: 3, c: 0 }, e: { r: 3, c: 11 } },
      ];

      // Set column widths
      ws['!cols'] = [
        { wch: 5 }, { wch: 15 }, { wch: 12 }, { wch: 8 }, { wch: 18 }, { wch: 18 },
        { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 12 },
      ];

      XLSX.utils.book_append_sheet(wb, ws, `منفست ${menafest.manafest_code}`);
      XLSX.writeFile(wb, savePath);

      return { success: true };
    } catch (error) {
      return { success: false, error: String(error) };
    }
  });
}

// ==================== HELPER FUNCTIONS ====================

function computeOrderStats(orders: Array<Record<string, unknown>>): Record<string, number> {
  const cashOrders = orders.filter(o => o.pay_type === 'تحصيل');
  const prepaidOrders = orders.filter(o => o.pay_type === 'مسبق');
  return {
    total_count: orders.length,
    total_items: orders.reduce((s, o) => s + Number(o.count || 0), 0),
    total_amount: orders.reduce((s, o) => s + Number(o.amount || 0), 0),
    total_cash_amount: cashOrders.reduce((s, o) => s + Number(o.amount || 0), 0),
    cash_count: cashOrders.length,
    total_prepaid_amount: prepaidOrders.reduce((s, o) => s + Number(o.amount || 0), 0),
    prepaid_count: prepaidOrders.length,
    total_anti_charger: orders.reduce((s, o) => s + Number(o.anti_charger || 0), 0),
    anti_charger_count: orders.filter(o => Number(o.anti_charger) > 0).length,
    total_transmitted: orders.reduce((s, o) => s + Number(o.transmitted || 0), 0),
    transmitted_count: orders.filter(o => Number(o.transmitted) > 0).length,
    total_miscellaneous: orders.reduce((s, o) => s + Number(o.miscellaneous || 0), 0),
    miscellaneous_count: orders.filter(o => Number(o.miscellaneous) > 0).length,
    total_discount: orders.reduce((s, o) => s + Number(o.discount || 0), 0),
    discount_count: orders.filter(o => Number(o.discount) > 0).length,
    paid_count: orders.filter(o => o.is_paid).length,
    unpaid_count: orders.filter(o => !o.is_paid).length,
    exist_count: orders.filter(o => o.is_exist).length,
  };
}

function buildManifestQuery(localCityId: number, filters: Record<string, unknown>, type: string): { all: Array<Record<string, unknown>> } {
  const db = getDb();
  const cityCol = type === 'incoming' ? 'to_city_id' : 'from_city_id';

  let query = `
    SELECT m.*, fc.name as from_city_name, tc.name as to_city_name,
           (SELECT COUNT(*) FROM orders WHERE menafest_id = m.id) as orders_count
    FROM menafests m
    JOIN cities fc ON m.from_city_id = fc.id
    JOIN cities tc ON m.to_city_id = tc.id
    WHERE m.${cityCol} = ?
  `;
  const params: unknown[] = [localCityId];

  if (filters.manafest_code) { query += ' AND m.manafest_code LIKE ?'; params.push(`%${filters.manafest_code}%`); }
  if (filters.city) {
    const otherCityCol = type === 'incoming' ? 'fc' : 'tc';
    query += ` AND ${otherCityCol}.name LIKE ?`;
    params.push(`%${filters.city}%`);
  }
  if (filters.driver_name) { query += ' AND m.driver_name LIKE ?'; params.push(`%${filters.driver_name}%`); }
  if (filters.car) { query += ' AND m.car LIKE ?'; params.push(`%${filters.car}%`); }
  if (filters.notes) { query += ' AND m.notes LIKE ?'; params.push(`%${filters.notes}%`); }
  if (filters.date_from) { query += ' AND date(m.created_at) >= ?'; params.push(filters.date_from); }
  if (filters.date_to) { query += ' AND date(m.created_at) <= ?'; params.push(filters.date_to); }

  query += ' ORDER BY m.created_at DESC';

  const all = db.prepare(query).all(...params) as Array<Record<string, unknown>>;
  return { all };
}

function applyManifestFilters(_query: string, _params: unknown[], _filters: Record<string, unknown>, _type: string): void {
  // This is handled by buildManifestQuery
}

function rebuildQuery(query: string, _params: unknown[], _filters: Record<string, unknown>, _type: string, _localCityId: number): string {
  return query;
}

function parseDamascus(rows: Array<Record<string, unknown>>): { success: boolean; orders: Array<Record<string, unknown>>; errors: string[] } {
  const orders: Array<Record<string, unknown>>[] = [];
  const errors: string[] = [];

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    if (!row['alaysal'] && !row['almrsl_alyh']) continue;

    const collection = row['althsyl'] ? Number(row['althsyl']) : 0;
    const prepaid = row['almdfoaa_msbka'] ? Number(row['almdfoaa_msbka']) : 0;

    let pay_type: string;
    let amount: number;

    if (collection > 0) {
      pay_type = 'تحصيل';
      amount = collection;
    } else if (prepaid > 0) {
      pay_type = 'مسبق';
      amount = prepaid;
    } else {
      pay_type = 'مسبق';
      amount = 0;
    }

    const order = {
      order_number: String(row['alaysal'] || '').trim(),
      content: String(row['alnoaa'] || 'طرد').trim(),
      count: isNaN(Number(row['alaadd'])) ? 1 : Number(row['alaadd']),
      sender: String(row['asm_almrsl'] || '').trim(),
      recipient: String(row['almrsl_alyh'] || '').trim(),
      pay_type,
      amount,
      anti_charger: isNaN(Number(row['dd_alshhn'])) ? 0 : Number(row['dd_alshhn']),
      transmitted: isNaN(Number(row['almhol'])) ? 0 : Number(row['almhol']),
      miscellaneous: isNaN(Number(row['mtfrkat_mtnoaa'])) ? 0 : Number(row['mtfrkat_mtnoaa']),
      discount: isNaN(Number(row['alkhsm'])) ? 0 : Number(row['alkhsm']),
      is_paid: false,
      is_exist: true,
      notes: '',
    };

    if (!order.order_number || !order.sender || !order.recipient) {
      errors.push(`صف ${i + 2}: بيانات ناقصة (رقم الإيصال أو المرسل أو المرسل إليه)`);
    } else {
      orders.push(order as unknown as Array<Record<string, unknown>>);
    }
  }

  return { success: true, orders: orders as unknown as Array<Record<string, unknown>>, errors };
}

function parseHalab(rows: Array<Record<string, unknown>>): { success: boolean; orders: Array<Record<string, unknown>>; errors: string[] } {
  const orders: Array<Record<string, unknown>>[] = [];
  const errors: string[] = [];

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const orderNumber = row['rkm_alashaaar'] ? String(row['rkm_alashaaar']) : String(row['almtslsl'] || '');

    const order = {
      order_number: orderNumber.trim(),
      content: String(row['noaa_altrd'] || 'طرد').trim(),
      count: isNaN(Number(row['alkmy'])) ? 1 : Number(row['alkmy']),
      sender: String(row['almrsl'] || '').trim(),
      recipient: String(row['almrsl_alyh'] || '').trim(),
      pay_type: String(row['aldfaa'] || 'مسبق').trim(),
      amount: isNaN(Number(row['alsafy_lldfaa'])) ? 0 : Number(row['alsafy_lldfaa']),
      anti_charger: isNaN(Number(row['dd_aldfaa'])) ? 0 : Number(row['dd_aldfaa']),
      transmitted: isNaN(Number(row['almhol'])) ? 0 : Number(row['almhol']),
      miscellaneous: isNaN(Number(row['tosyl'])) ? 0 : Number(row['tosyl']),
      discount: isNaN(Number(row['alkhsm'])) ? 0 : Number(row['alkhsm']),
      is_paid: false,
      is_exist: true,
      notes: '',
    };

    if (!order.order_number || !order.sender || !order.recipient) {
      errors.push(`صف ${i + 2}: بيانات ناقصة`);
    } else {
      orders.push(order as unknown as Array<Record<string, unknown>>);
    }
  }

  return { success: true, orders: orders as unknown as Array<Record<string, unknown>>, errors };
}
