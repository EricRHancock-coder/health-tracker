# Health Tracker - Project Status

**Last Updated:** 2026-04-21  
**Status:** Planning Complete - Ready for Implementation

---

## Decisions Finalized

### Architecture
| Aspect | Decision |
|--------|----------|
| **Architecture Pattern** | SPA (Single Page Application) |
| **Backend** | PHP 8.2+ with custom MVC |
| **Frontend** | React 18 + Bootstrap 5 + React Router |
| **Database** | SQLite with PHP-level encryption (defuse/php-encryption) |
| **Authentication** | JWT tokens |
| **Build Tool** | Vite |

### Roles & Permissions
- **Administrator**: Full CRUD on users, residents, medications. View audit logs.
- **ReadWrite**: View residents. Create daily records (bathing, cognitive, mood, medications).
- **ReadOnly**: View-only access to residents and records.

---

## Data Model

### Daily Tracking Fields

| Category | Fields | Comment |
|----------|--------|---------|
| **Bathing** | `taken` (boolean), `type` (enum: Short/Long/Unknown) | Yes |
| **Cognitive State** | `state` (enum: Alert/Mild/Moderate/Severe confusion) | Yes |
| **Mood** | `mood` (enum: Good/Neutral/Distressed) | Yes |
| **Medication** | `time_slot` (Morning/Afternoon/Night), `status` (Taken/Refused/Not Taken), `not_taken_reason` | Yes |

### Excluded
- Vital signs
- Sleep tracking
- Food intake
- Agitation level
- Alzheimer's specific behaviors (sundowning, wandering, hallucinations, etc.)
- Other ADLs (only bathing tracked)

---

## Features

### Admin Features
- User management (CRUD)
- Resident management (CRUD)
- Medication configuration:
  - Add/edit/remove medications (name, dosage, instructions)
  - Set schedule per medication (Morning/Afternoon/Night time slots)

### ReadWrite User Features
- View resident list and details
- Submit daily records per category (bathing, cognitive, mood)
- Submit medication records per time slot
  - Mark as: Taken, Refused, or Not Taken
  - Required reason if "Not Taken"
  - Optional comment field

### ReadOnly User Features
- View resident list and details
- View daily records (read-only)

### Audit Logging
- All CREATE, UPDATE, DELETE actions logged
- Fields: user_id, action, table_name, record_id, old_values (JSON), new_values (JSON), ip_address, timestamp
- Retention: Forever

---

## Database Schema

```sql
-- Users
users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('admin', 'readwrite', 'readonly')),
    full_name TEXT NOT NULL,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Residents
residents (
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

-- Medications (configured by Admin)
medications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resident_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    dosage TEXT,
    instructions TEXT,
    morning BOOLEAN DEFAULT 0,
    afternoon BOOLEAN DEFAULT 0,
    night BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id)
);

-- Daily Records (bathing, cognitive, mood)
daily_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resident_id INTEGER NOT NULL,
    recorded_by INTEGER NOT NULL,
    record_date DATE NOT NULL,
    -- Bathing
    bathing_taken BOOLEAN,
    bathing_type TEXT CHECK(bathing_type IN ('Short', 'Long', 'Unknown')),
    bathing_comment TEXT,
    -- Cognitive
    cognitive_state TEXT CHECK(cognitive_state IN ('Alert', 'Mild confusion', 'Moderate confusion', 'Severe confusion')),
    cognitive_comment TEXT,
    -- Mood
    mood_state TEXT CHECK(mood_state IN ('Good', 'Neutral', 'Distressed')),
    mood_comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Medication Records (per time slot)
medication_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resident_id INTEGER NOT NULL,
    medication_id INTEGER NOT NULL,
    recorded_by INTEGER NOT NULL,
    record_date DATE NOT NULL,
    time_slot TEXT CHECK(time_slot IN ('Morning', 'Afternoon', 'Night')),
    status TEXT CHECK(status IN ('Taken', 'Refused', 'Not Taken')),
    not_taken_reason TEXT,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id),
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Activity Log (audit trail)
activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL CHECK(action IN ('CREATE', 'UPDATE', 'DELETE')),
    table_name TEXT NOT NULL,
    record_id INTEGER,
    old_values TEXT, -- JSON
    new_values TEXT, -- JSON
    ip_address TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## API Endpoints

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/auth/login | POST | Public | JWT login |
| /api/auth/logout | POST | Auth | Logout |
| /api/auth/me | GET | Auth | Current user info |
| /api/users | GET | Admin | List users |
| /api/users | POST | Admin | Create user |
| /api/users/:id | PUT | Admin | Update user |
| /api/users/:id | DELETE | Admin | Delete user |
| /api/residents | GET | Auth | List residents |
| /api/residents | POST | Admin | Create resident |
| /api/residents/:id | GET | Auth | Get resident details |
| /api/residents/:id | PUT | Admin | Update resident |
| /api/residents/:id | DELETE | Admin | Delete resident |
| /api/residents/:id/medications | GET | Auth | List medications for resident |
| /api/residents/:id/medications | POST | Admin | Add medication |
| /api/residents/:id/medications/:medId | PUT | Admin | Update medication |
| /api/residents/:id/medications/:medId | DELETE | Admin | Delete medication |
| /api/residents/:id/daily-records | GET | Auth | Get daily records |
| /api/residents/:id/daily-records | POST | ReadWrite | Submit daily record |
| /api/residents/:id/medication-records | POST | ReadWrite | Submit medication record |
| /api/audit-log | GET | Admin | View audit log |

---

## Implementation Phases

### Phase 1: Backend Foundation
- [ ] Project structure setup
- [ ] Database connection & encryption setup
- [ ] JWT authentication middleware
- [ ] RBAC middleware
- [ ] UserController with audit logging
- [ ] Response and Encryption utilities

### Phase 2: Core Data Models
- [ ] Resident model & controller
- [ ] Medication model & controller (Admin only)
- [ ] DailyRecord model & controller
- [ ] MedicationRecord model & controller

### Phase 3: Frontend Setup
- [ ] React app with Vite + Bootstrap
- [ ] Auth context & login page
- [ ] Route protection based on roles
- [ ] Layout components (sidebar, header)

### Phase 4: Frontend Pages
- [ ] Dashboard (resident list)
- [ ] Resident detail view
- [ ] Daily record forms (bathing, cognitive, mood)
- [ ] Medication submission forms (per time slot)
- [ ] Admin: User management
- [ ] Admin: Medication configuration
- [ ] Admin: Audit log viewer

### Phase 5: Polish
- [ ] Form validation
- [ ] Error handling
- [ ] Loading states
- [ ] Testing

---

## Project Structure

```
health-tracker/
├── backend/
│   ├── config/
│   │   ├── database.php
│   │   ├── auth.php
│   │   └── encryption.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ResidentController.php
│   │   ├── MedicationController.php
│   │   ├── DailyRecordController.php
│   │   └── AuditController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Resident.php
│   │   ├── Medication.php
│   │   ├── DailyRecord.php
│   │   ├── MedicationRecord.php
│   │   └── AuditLog.php
│   ├── middleware/
│   │   ├── AuthMiddleware.php
│   │   └── RBACMiddleware.php
│   ├── utils/
│   │   ├── Response.php
│   │   ├── Validator.php
│   │   └── Encryption.php
│   ├── database/
│   │   └── health_tracker.db
│   └── public/
│       ├── index.php
│       └── .htaccess
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── context/
│   │   ├── hooks/
│   │   ├── services/
│   │   └── App.jsx
│   ├── public/
│   └── package.json
├── docs/
└── docker-compose.yml
```

---

## Tech Stack Summary

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+ |
| Frontend | React 18 |
| UI | Bootstrap 5 + React-Bootstrap |
| Build Tool | Vite |
| Database | SQLite 3 |
| Auth | JWT (firebase/php-jwt) |
| Encryption | defuse/php-encryption |
| HTTP Client | Axios |

---

## Next Steps
1. Create project structure (backend and frontend directories)
2. Initialize PHP backend with composer
3. Set up React frontend with Vite
4. Implement Phase 1: Backend Foundation

---

*Document generated from planning session on 2026-04-21*
