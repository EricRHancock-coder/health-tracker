# AGENTS.md - Health Tracker

## Core Architecture
- **Backend**: PHP 8.2+ custom MVC API in `backend/`. Web root is `backend/public/`.
- **Frontend**: React 18 SPA in `frontend/` built with Vite.
- **Database**: SQLite 3 in `backend/database/health_tracker.db`. Uses PHP-level encryption for sensitive data.
- **Auth**: JWT-based (4-hour expiry). Roles: `admin`, `readwrite`, `readonly`.

## Developer Commands
- **Start Backend**: `cd backend && php -S localhost:8080 -t public/`
- **Start Frontend**: `cd frontend && npm run dev`
- **Build Frontend**: `cd frontend && npm run build`
- **Reset/Seed DB**: `cd backend && rm database/health_tracker.db database/.encryption_key && php seed.php`
- **Kill PHP Server**: `pkill -f "php -S localhost:8080"`

## High-Signal Context
- **API Entrypoint**: `backend/public/index.php`
- **Frontend Entrypoint**: `frontend/src/main.jsx`
- **Record Locking**: Prevents concurrent edits on `DailyRecord` and `MedicationRecord`. Locks expire after 4 hours.
- **Audit Trail**: All CRUD and login actions are logged in the `activity_log` table.
- **Sensitive Data**: Do not attempt to read the database directly without understanding the encryption layer (`backend/config/encryption.php`).

## Reference Documentation
- `docs/API.md`: Full API endpoint details.
- `docs/SCHEMA.md`: Database schema.
- `docs/IMPLEMENTATION_SUMMARY.md`: Technical implementation deep-dive.
