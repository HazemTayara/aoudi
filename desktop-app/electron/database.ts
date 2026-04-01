import Database from 'better-sqlite3';
import * as path from 'path';
import * as fs from 'fs';
import { app } from 'electron';

let db: Database.Database;

function getDbPath(): string {
  // Use app.getPath('userData') for a static, per-user location
  // On Windows: C:\Users\<user>\AppData\Roaming\aoudi-shipping
  const userDataPath = app.getPath('userData');
  if (!fs.existsSync(userDataPath)) {
    fs.mkdirSync(userDataPath, { recursive: true });
  }
  return path.join(userDataPath, 'aoudi_shipping.db');
}

export function getDb(): Database.Database {
  if (!db) {
    throw new Error('Database not initialized. Call initDatabase() first.');
  }
  return db;
}

export function initDatabase(): void {
  const dbPath = getDbPath();
  console.log('Database path:', dbPath);

  db = new Database(dbPath);

  // Enable WAL mode for better performance
  db.pragma('journal_mode = WAL');
  db.pragma('foreign_keys = ON');

  // Run migrations
  runMigrations();
}

function runMigrations(): void {
  // Create migrations tracking table
  db.exec(`
    CREATE TABLE IF NOT EXISTS _migrations (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL UNIQUE,
      applied_at TEXT DEFAULT (datetime('now'))
    )
  `);

  const migrations: Array<{ name: string; sql: string }> = [
    {
      name: '001_create_cities_table',
      sql: `
        CREATE TABLE IF NOT EXISTS cities (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL,
          is_local INTEGER NOT NULL DEFAULT 0,
          created_at TEXT DEFAULT (datetime('now')),
          updated_at TEXT DEFAULT (datetime('now'))
        )
      `,
    },
    {
      name: '002_create_drivers_table',
      sql: `
        CREATE TABLE IF NOT EXISTS drivers (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL,
          notes TEXT DEFAULT NULL,
          created_at TEXT DEFAULT (datetime('now')),
          updated_at TEXT DEFAULT (datetime('now'))
        )
      `,
    },
    {
      name: '003_create_menafests_table',
      sql: `
        CREATE TABLE IF NOT EXISTS menafests (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          from_city_id INTEGER NOT NULL,
          to_city_id INTEGER NOT NULL,
          manafest_code TEXT NOT NULL,
          driver_name TEXT NOT NULL,
          car TEXT NOT NULL,
          notes TEXT DEFAULT NULL,
          created_at TEXT DEFAULT (datetime('now')),
          updated_at TEXT DEFAULT (datetime('now')),
          FOREIGN KEY (from_city_id) REFERENCES cities(id) ON DELETE CASCADE,
          FOREIGN KEY (to_city_id) REFERENCES cities(id) ON DELETE CASCADE
        )
      `,
    },
    {
      name: '004_create_orders_table',
      sql: `
        CREATE TABLE IF NOT EXISTS orders (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          menafest_id INTEGER NOT NULL,
          driver_id INTEGER DEFAULT NULL,
          order_number TEXT NOT NULL,
          content TEXT DEFAULT 'طرد',
          count INTEGER DEFAULT 1,
          sender TEXT NOT NULL,
          recipient TEXT NOT NULL,
          pay_type TEXT NOT NULL CHECK(pay_type IN ('مسبق', 'تحصيل')),
          amount REAL DEFAULT 0,
          anti_charger REAL DEFAULT 0,
          transmitted REAL DEFAULT 0,
          miscellaneous REAL DEFAULT 0,
          discount REAL DEFAULT 0,
          is_paid INTEGER DEFAULT 0,
          paid_at TEXT DEFAULT NULL,
          is_exist INTEGER DEFAULT 1,
          notes TEXT DEFAULT NULL,
          assigned_at TEXT DEFAULT NULL,
          created_at TEXT DEFAULT (datetime('now')),
          updated_at TEXT DEFAULT (datetime('now')),
          FOREIGN KEY (menafest_id) REFERENCES menafests(id) ON DELETE CASCADE,
          FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL
        )
      `,
    },
    {
      name: '005_create_settings_table',
      sql: `
        CREATE TABLE IF NOT EXISTS settings (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          key TEXT NOT NULL UNIQUE,
          value TEXT DEFAULT NULL,
          created_at TEXT DEFAULT (datetime('now')),
          updated_at TEXT DEFAULT (datetime('now'))
        )
      `,
    },
  ];

  const appliedStmt = db.prepare('SELECT name FROM _migrations WHERE name = ?');
  const insertStmt = db.prepare('INSERT INTO _migrations (name) VALUES (?)');

  for (const migration of migrations) {
    const existing = appliedStmt.get(migration.name) as { name: string } | undefined;
    if (!existing) {
      console.log(`Applying migration: ${migration.name}`);
      db.exec(migration.sql);
      insertStmt.run(migration.name);
    }
  }

  console.log('Database migrations complete.');
}

export function getDbPath_export(): string {
  return getDbPath();
}
