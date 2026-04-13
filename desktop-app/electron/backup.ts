import * as fs from 'fs';
import * as path from 'path';
import { app } from 'electron';
import { getDbPath_export } from './database';

const BACKUP_INTERVAL_MS = 14 * 24 * 60 * 60 * 1000; // 2 weeks
const MAX_BACKUPS = 10;

function getBackupDir(): string {
  const backupDir = path.join(app.getPath('userData'), 'backups');
  if (!fs.existsSync(backupDir)) {
    fs.mkdirSync(backupDir, { recursive: true });
  }
  return backupDir;
}

export function performBackup(): string {
  const dbPath = getDbPath_export();
  const backupDir = getBackupDir();
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const backupFileName = `aoudi_shipping_backup_${timestamp}.db`;
  const backupPath = path.join(backupDir, backupFileName);

  // Copy the database file
  fs.copyFileSync(dbPath, backupPath);

  // Clean up old backups
  cleanOldBackups(backupDir);

  console.log(`Backup created: ${backupPath}`);
  return backupPath;
}

function cleanOldBackups(backupDir: string): void {
  const files = fs.readdirSync(backupDir)
    .filter(f => f.startsWith('aoudi_shipping_backup_') && f.endsWith('.db'))
    .map(f => ({
      name: f,
      path: path.join(backupDir, f),
      time: fs.statSync(path.join(backupDir, f)).mtime.getTime(),
    }))
    .sort((a, b) => b.time - a.time); // Newest first

  // Remove old backups beyond MAX_BACKUPS
  if (files.length > MAX_BACKUPS) {
    for (let i = MAX_BACKUPS; i < files.length; i++) {
      fs.unlinkSync(files[i].path);
      console.log(`Deleted old backup: ${files[i].name}`);
    }
  }
}

export function setupBackupSchedule(): void {
  // Check if we need to backup on startup
  const backupDir = getBackupDir();
  const files = fs.readdirSync(backupDir)
    .filter(f => f.startsWith('aoudi_shipping_backup_') && f.endsWith('.db'))
    .map(f => ({
      name: f,
      time: fs.statSync(path.join(backupDir, f)).mtime.getTime(),
    }))
    .sort((a, b) => b.time - a.time);

  const now = Date.now();
  const lastBackupTime = files.length > 0 ? files[0].time : 0;

  if (now - lastBackupTime >= BACKUP_INTERVAL_MS) {
    console.log('Auto-backup triggered (2 weeks since last backup).');
    performBackup();
  }

  // Schedule periodic check every 24 hours
  setInterval(() => {
    const dir = getBackupDir();
    const backupFiles = fs.readdirSync(dir)
      .filter(f => f.startsWith('aoudi_shipping_backup_') && f.endsWith('.db'))
      .map(f => ({
        name: f,
        time: fs.statSync(path.join(dir, f)).mtime.getTime(),
      }))
      .sort((a, b) => b.time - a.time);

    const currentTime = Date.now();
    const lastTime = backupFiles.length > 0 ? backupFiles[0].time : 0;

    if (currentTime - lastTime >= BACKUP_INTERVAL_MS) {
      console.log('Scheduled auto-backup triggered.');
      performBackup();
    }
  }, 24 * 60 * 60 * 1000); // Check daily
}
