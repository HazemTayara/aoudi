# شحن العودة - Aoudi Shipping Desktop App

A standalone Windows desktop application for managing return shipping operations, built with Electron + React + TypeScript + SQLite.

## Features

- **Manifest Management**: Create and manage incoming/outgoing shipping manifests
- **Order Management**: Full CRUD operations for orders with filtering, pagination, and statistics
- **Driver Management**: Assign/detach orders to drivers with batch operations
- **City Management**: Configure cities and set the local (home) city
- **Excel Import/Export**: Import orders from `.xlsx` files (Damascus & Halab formats), export manifest reports
- **Settlement (تشطيب)**: Quick order lookup and payment confirmation workflow
- **Automatic Backups**: Bi-weekly automatic database backups with manual trigger option
- **100% Offline**: No internet connection required - all data stored locally

## Tech Stack

- **Frontend**: React 18 + TypeScript + Bootstrap 5 RTL
- **Desktop**: Electron 33
- **Database**: SQLite via better-sqlite3
- **Excel**: SheetJS (xlsx)
- **Build**: Vite + Electron Builder
- **Installer**: NSIS (Windows)

## Development Setup

### Prerequisites

- Node.js 18+ 
- npm 9+

### Install Dependencies

```bash
cd desktop-app
npm install
```

### Development Mode

```bash
# Start Vite dev server + Electron in watch mode
npm run dev

# In another terminal, start Electron
npm start -- --dev
```

### Build for Production

```bash
# Build both frontend and electron
npm run build

# Create Windows installer
npm run dist:win
```

### Type Checking

```bash
npm run lint
```

## Project Structure

```
desktop-app/
├── electron/              # Electron main process
│   ├── main.ts           # App entry, window creation
│   ├── preload.ts        # Context bridge (IPC API)
│   ├── database.ts       # SQLite init, migrations
│   ├── ipc-handlers.ts   # All business logic (controllers)
│   └── backup.ts         # Backup scheduling & execution
├── src/                   # React renderer process
│   ├── components/       # Reusable UI components
│   ├── pages/            # Page components (routes)
│   ├── types/            # TypeScript type definitions
│   ├── utils/            # Utility functions
│   ├── styles/           # CSS styles
│   ├── App.tsx           # Router setup
│   └── main.tsx          # React entry point
├── assets/               # Static assets (icons)
├── dist/                 # Vite build output
├── dist-electron/        # Electron build output
└── release/              # Installer output
```

## Data & Backup Locations

### Database
- **Windows**: `%APPDATA%\aoudi-shipping\aoudi_shipping.db`
- The database file location is static and cannot be moved by the user
- Data is preserved across app updates

### Backups
- **Windows**: `%APPDATA%\aoudi-shipping\backups\`
- Format: `aoudi_shipping_backup_YYYY-MM-DDTHH-MM-SS.db`
- Automatic backup every 2 weeks
- Maximum 10 backups retained (oldest deleted automatically)
- Manual backup available from Settings page or Home page

## Update Process

1. Download the new installer `.exe`
2. Run the installer - it will detect and update the existing installation
3. **Database is preserved** - no data loss during updates
4. The installer does NOT require admin privileges

## Architecture Notes

### Laravel to Electron Mapping

| Laravel Component | Electron Equivalent |
|---|---|
| Controllers | IPC Handlers (`electron/ipc-handlers.ts`) |
| Eloquent Models | Direct SQL via better-sqlite3 |
| Blade Views | React Components (`src/pages/`) |
| Routes | React Router + IPC channels |
| Migrations | SQLite migrations (`electron/database.ts`) |
| maatwebsite/excel | SheetJS (`xlsx` library) |
| Database (MySQL) | SQLite (local file) |
| Auth middleware | Removed (no authentication) |

### Excel Import Formats

The app supports two Excel import formats matching the original Laravel implementation:

1. **Damascus format**: Columns include `alaysal`, `almrsl_alyh`, `althsyl`, `almdfoaa_msbka`, etc.
2. **Halab format**: Columns include `rkm_alashaaar`, `almtslsl`, `noaa_altrd`, `alkmy`, etc.

The format is auto-detected based on the manifest's source city name.
