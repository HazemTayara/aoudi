import { app, BrowserWindow, ipcMain, dialog } from 'electron';
import * as path from 'path';
import { initDatabase } from './database';
import { registerIpcHandlers } from './ipc-handlers';
import { setupBackupSchedule, performBackup } from './backup';

let mainWindow: BrowserWindow | null = null;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    minWidth: 1024,
    minHeight: 700,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
    },
    icon: path.join(__dirname, '..', 'assets', 'icon.png'),
    title: 'شحن العودة',
  });

  // In development, load from Vite dev server
  if (process.env.NODE_ENV === 'development' || process.argv.includes('--dev')) {
    mainWindow.loadURL('http://localhost:5173');
    mainWindow.webContents.openDevTools();
  } else {
    // In production, load from built files
    mainWindow.loadFile(path.join(__dirname, '..', 'dist', 'index.html'));
  }

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(() => {
  // Initialize database
  initDatabase();

  // Register IPC handlers
  registerIpcHandlers();

  // Setup backup schedule
  setupBackupSchedule();

  // Manual backup handler
  ipcMain.handle('backup:manual', async () => {
    try {
      const result = performBackup();
      return { success: true, path: result };
    } catch (error) {
      return { success: false, error: String(error) };
    }
  });

  // File dialog handlers
  ipcMain.handle('dialog:openFile', async (_event, filters) => {
    const result = await dialog.showOpenDialog({
      properties: ['openFile'],
      filters: filters || [{ name: 'Excel Files', extensions: ['xlsx', 'xls'] }],
    });
    return result;
  });

  ipcMain.handle('dialog:saveFile', async (_event, defaultName, filters) => {
    const result = await dialog.showSaveDialog({
      defaultPath: defaultName,
      filters: filters || [{ name: 'Excel Files', extensions: ['xlsx'] }],
    });
    return result;
  });

  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
