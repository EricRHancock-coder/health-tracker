# Health Tracker - Alzheimer's Care Application

> Project Context Document | Created: 2026-04-21

---

## Project Overview

A PHP-based web application for tracking health status of residents with Alzheimer's disease in a communal care setting. Features role-based access control (RBAC), encrypted SQLite storage, and a modern React frontend with Bootstrap styling.

---

## Architecture

### Backend: PHP MVC API
- **Framework**: Custom MVC pattern (lightweight, no heavy frameworks)
- **API Style**: RESTful JSON API
- **Authentication**: JWT (JSON Web Tokens)
- **Entry Point**: `backend/public/index.php`

### Frontend: React SPA
- **Framework**: React 18 + React Router
- **UI Library**: Bootstrap 5 + React-Bootstrap
- **Build Tool**: Vite or Create React App
- **State Management**: React Context or Zustand

### Database: Encrypted SQLite
- **Options**:
  1. **SQLCipher** - Requires PHP extension, stronger encryption
  2. **PHP-level encryption** - Encrypt/decrypt in application layer, easier deployment

---

## User Roles & Permissions

| Role | Users | Residents | Health Data | Notes |
|------|-------|-----------|-------------|-------|
| **Administrator** | CRUD | CRUD | CRUD | Full access, can manage other users |
| **Read/Write** | Read | Read/Write | Read/Write | Can update resident health records |
| **Read-Only** | Read | Read | Read | View-only access for caregivers |

**Legend**: CRUD = Create, Read, Update, Delete

---

## Project Structure

```
health-tracker/
├── backend/                    # PHP API Backend
│   ├── config/
│   │   ├── database.php       # SQLite connection & encryption
│   │   └── auth.php           # JWT configuration
│   ├── controllers/           # MVC Controllers
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ResidentController.php
│   │   └── HealthDataController.php
│   ├── models/                # Data Models
│   │   ├── User.php
│   │   ├── Resident.php
│   │   └── HealthRecord.php
│   ├── middleware/            # Auth & RBAC Middleware
│   │   ├── AuthMiddleware.php
│   │   └── RBACMiddleware.php
│   ├── database/              # Database files
│   │   └── health_tracker.db
│   ├── utils/                 # Helper functions
│   └── public/                # Web root
│       ├── index.php          # API entry point
│       └── .htaccess          # URL rewriting
├── frontend/                  # React Frontend
│   ├── src/
│   │   ├── components/        # Reusable UI components
│   │   │   ├── Layout/
│   │   │   ├── Forms/
│   │   │   └── Charts/
│   │   ├── pages/             # Route pages
│   │   │   ├── Login.jsx
│   │   │   ├── Dashboard.jsx
│   │   │   ├── Residents/
│   │   │   └── Admin/
│   │   ├── hooks/             # Custom React hooks
│   │   ├── context/           # React Context (auth, etc.)
│   │   ├── services/          # API service layer
│   │   │   └── api.js
│   │   └── App.jsx
│   └── public/
├── docs/                      # Additional documentation
└── docker-compose.yml         # Optional: Local dev environment
```

---

## Database Schema (Proposed)

### Tables

```sql
-- Users table
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('admin', 'readwrite', 'readonly')),
    full_name TEXT NOT NULL,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Residents table
CREATE TABLE residents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    date_of_birth DATE,
    medical_record_number TEXT UNIQUE,
    room_number TEXT,
    emergency_contact_name TEXT,
    emergency_contact_phone TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Health records table
CREATE TABLE health_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resident_id INTEGER NOT NULL,
    recorded_by INTEGER NOT NULL,  -- user_id
    record_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Vital signs
    blood_pressure_systolic INTEGER,
    blood_pressure_diastolic INTEGER,
    heart_rate INTEGER,
    temperature REAL,
    -- Behavioral/Activity metrics
    mood TEXT,
    sleep_quality TEXT,
    appetite TEXT,
    mobility TEXT,
    agitation_level TEXT,
    -- Notes
    notes TEXT,
    -- Metadata
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Activity log for audit trail
CREATE TABLE activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    table_name TEXT,
    record_id INTEGER,
    old_values TEXT,  -- JSON
    new_values TEXT,  -- JSON
    ip_address TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## API Endpoints (Proposed)

### Authentication
- `POST /api/auth/login` - Login, returns JWT
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### Users (Admin only)
- `GET /api/users` - List all users
- `POST /api/users` - Create user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### Residents
- `GET /api/residents` - List residents (all roles)
- `POST /api/residents` - Create resident (Admin/ReadWrite)
- `GET /api/residents/{id}` - Get resident details
- `PUT /api/residents/{id}` - Update resident (Admin/ReadWrite)
- `DELETE /api/residents/{id}` - Delete resident (Admin only)

### Health Records
- `GET /api/residents/{id}/health-records` - Get health history
- `POST /api/health-records` - Create health record (Admin/ReadWrite)
- `PUT /api/health-records/{id}` - Update record (Admin/ReadWrite)
- `DELETE /api/health-records/{id}` - Delete record (Admin only)

---

## Frontend Routes

| Route | Component | Access |
|-------|-----------|--------|
| `/login` | LoginPage | Public |
| `/` | Dashboard | All authenticated |
| `/residents` | ResidentsList | All authenticated |
| `/residents/:id` | ResidentDetails | All authenticated |
| `/residents/:id/records` | HealthRecords | All authenticated |
| `/residents/:id/records/new` | CreateRecord | Admin, ReadWrite |
| `/admin/users` | UserManagement | Admin only |
| `/admin/users/new` | CreateUser | Admin only |
| `/admin/audit-log` | AuditLog | Admin only |

---

## Open Questions & Decisions

### Architecture
- [ ] **SPA vs Multi-page**: Single Page App (React Router) OR traditional PHP with React components?
- [ ] **PHP Framework**: Custom lightweight MVC OR use Laravel/Slim?

### Database
- [ ] **Encryption Method**: SQLCipher (requires extension) OR PHP-level encryption?
- [ ] **Hosting Environment**: Shared hosting, VPS, or cloud? (affects encryption choice)

### Health Data
- [ ] **Metrics to Track**: Which specific health indicators?
  - Vital signs (BP, HR, temp)?
  - Medication tracking?
  - Behavioral observations (mood, agitation, sleep)?
  - Daily activities (eating, bathing, mobility)?

### Scope
- [ ] **Residents**: Single resident or multiple residents?
- [ ] **Notifications**: Email/SMS alerts for critical values?
- [ ] **Reports**: Charts, PDF exports, trends?

---

## Tech Stack Summary

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | PHP | 8.2+ |
| Frontend | React | 18+ |
| UI | Bootstrap | 5.x |
| Database | SQLite | 3.x |
| Auth | JWT | - |
| HTTP | Apache/Nginx | - |

---

## Implementation Roadmap

### Phase 1: Foundation
1. Set up project structure
2. Configure PHP backend with MVC pattern
3. Implement JWT authentication
4. Set up SQLite with encryption

### Phase 2: Core Features
1. User management (CRUD)
2. Resident management
3. Health record CRUD
4. RBAC middleware

### Phase 3: Frontend
1. React app setup with Bootstrap
2. Login page
3. Dashboard layout
4. Resident management pages
5. Health record forms

### Phase 4: Polish
1. Audit logging
2. Charts/visualizations
3. Testing
4. Documentation

---

## Next Steps

1. **Decide on open questions** (see above)
2. **Choose architecture approach** (SPA vs traditional)
3. **Select encryption method** based on hosting constraints
4. **Define specific health metrics** to track
5. **Begin Phase 1 implementation**

---

*Document created for planning purposes. Update as decisions are made.*
