# Health Tracker - Project Plan

> Alzheimer's Care Application | Last Updated: 2026-04-28 (Implemented Token Blacklisting & Verified Repository)

---

## Current Progress

| Component | Status | Details |
|-----------|--------|---------|
| **UI/UX Design** | ✅ Completed | Dashboard layouts, navigation, login screen, reports portal mockups defined |
| **Database Schema** | ✅ Completed | All tables defined: users, residents, medications, daily_records, medication_records, audit_log, token_blacklist |
| **Backend (ORM)** | ✅ Completed | Migrated from PDO to RedBeanPHP (User, AuditLog, TokenBlacklist) |
| **User Model** | ✅ Completed | Auto-logging, soft-delete, authentication with failure tracking |
| **AuditLog Model** | ✅ Completed | CREATE/UPDATE/DELETE/LOGIN/FAILED_LOGIN actions, JSON old/new values |
| **AuthMiddleware** | ✅ Completed | Validates JWT and account status |
| **JwtHandler** | ✅ Completed | JWT lifecycle utility implemented |
| **Residents Table** | ⏳ Pending | Awaiting implementation |
| **Medications Table** | ⏳ Pending | Awaiting implementation |
| **Daily Records Table** | ⏳ Pending | Awaiting implementation |
| **Medication Records Table** | ⏳ Pending | Awaiting implementation |
| **LockService** | ⏳ Pending | Record locking utility |
| **AuthController** | ✅ Completed | JWT authentication endpoints implemented with secure error handling |
| **JwtHandler** | ✅ Completed | JWT lifecycle utility implemented |
| **UserController** | ⏳ Pending | User management API |
| **Frontend Setup** | ⏳ Pending | React + Vite + Bootstrap initialization |

---

## Project Overview

A PHP-based web application for tracking health status of residents with Alzheimer's disease in a communal care setting. Features role-based access control (RBAC), encrypted SQLite storage, and a modern React frontend with Bootstrap styling.

---

## Architecture Decisions

| Aspect | Decision |
|--------|----------|
| **Pattern** | Single Page Application (SPA) |
| **Backend** | PHP 8.2+ with Flight micro-framework and RedBeanPHP ORM |
| **Frontend** | React 18 + Bootstrap 5 + React Router |
| **Build Tool** | Vite |
| **Database** | SQLite 3 with PHP-level encryption (defuse/php-encryption) |
| **Authentication** | JWT tokens (firebase/php-jwt) |
| **HTTP Client** | Axios |

### Authentication Configuration (Temporary)

**NOTE: The following configuration is for development purposes only and must be updated before production.**

| Parameter | Decision | Future Options |
| :--- | :--- | :--- |
| **Secret Key** | Hardcoded (Temporary) | Use environment variables (`.env`) |
| **Algorithm** | HS256 (Symmetric) | RS256 (Asymmetric) |
| **Token Expiration** | 24 Hours | Configurable TTL |
| **Token Strategy** | Single Token (Re-auth required) | Refresh Token pattern (Access + Refresh tokens) |

#### [UNDECIDED] JWT Invalidation Strategy

We need to decide on a strategy for `invalidateServerSideSession()`. 
Options considered:
1. **Token Blacklisting**: Store invalidated tokens in a fast-access cache (e.g., Redis) until they expire.
2. **User-Based Versioning**: Include a `token_version` in the JWT and database. Increment version on logout.
3. **Refresh Token Revocation**: Use short-lived access tokens and long-lived refresh tokens. Revoke the refresh token on logout.

---

## Application Workflow

### Navigation by Role

| Role | Menu Items |
|------|------------|
| **Administrator** | Dashboard, Users, Residents, Reports, Medications, Audit |
| **Caregiver** (Read/Write) | Dashboard, Residents, Reports |
| **Read-Only** | Dashboard, Residents, Reports, View Records |

### User Flows

#### Residents Workflow
```
Login -> Residents
```
- Admin: Can Create/Edit/Delete residents
- Caregiver: Can edit residents
- Read-Only: can view but not modify

#### Authentication Flow
```
Login → Validate Credentials → JWT Token → Dashboard
```
- Login accepts email and password only
- Vague error message: "Invalid email or password" (security best practice)
- No self-registration or password recovery (admin-managed accounts)
- Session timeout handling: redirect to login

#### Daily Record Workflow
```
Dashboard → Residents → Select Resident → Create/Edit Daily Record
```
- **Admin/Caregiver:** Can create/edit and Delete daily records
- **Read-Only:** Can view but not modify
- Records must be accessed via Resident detail page

#### Medication Administration Workflow
```
Dashboard → Residents → Select Resident → Administer Medication
```
- **Admin:** Can configure/add medications and mark as taken/refused/not taken
- **Caregiver:** Can mark medications as taken/refused/not taken
- **Read-Only:** Can view medication records only

#### Record Locking Workflow
```
Edit Record → Check Lock Status → Acquire Lock (1hr) → Save/Release Lock
```
- All users can view locked records (Type, Resident, Locked By, Locked At, Expires)
- **Admin**: Can release any any and all locks.
- **Caregiver:** Can release own locks (individual or "Release All")
- **Read-Only:** Cannot release locks
- Automatic expiration after 1 hour

### Dashboard Structure

All dashboards display:
1. **Most Active Residents** (top section) - 5 residents with most recent activity
2. **Locked Records** - All currently locked records
3. **Role-specific sections:**
   - **Admin:** Quick Actions, Recent Audit Activity
   - **Caregiver:** Recent Activity
   - **Read-Only:** Recent Daily Records

### Reports Portal

Placeholder page accessible to all roles. Displays "Coming Soon" message for future reporting features.

---

## User Roles & Permissions

| Role | Users | Residents | Daily Records | Medications | Notes |
|------|-------|-----------|---------------|-------------|-------|
| **Administrator** | CRUD | CRUD | CRUD | CRUD | Full access, view audit logs |
| **Read/Write** | Read | Read | CRUD | Create | Submit and modify daily records |
| **Read-Only** | Read | Read | Read | Read | View-only access |

**Legend**: CRUD = Create, Read, Update, Delete

---

## Data Model

### Core Entities

#### User Model
| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | INTEGER | PRIMARY KEY | Unique identifier |
| `email` | TEXT | UNIQUE, NOT NULL | Login email address |
| `password_hash` | TEXT | NOT NULL | Bcrypt hashed password |
| `role` | TEXT | NOT NULL, CHECK | admin, readwrite, or readonly |
| `full_name` | TEXT | NOT NULL | Display name |
| `is_verified` | BOOLEAN | NOT NULL, DEFAULT 0 | Account verified by admin |
| `is_disabled` | BOOLEAN | NOT NULL, DEFAULT 0 | Soft delete flag |
| `last_login_at` | DATETIME | | Last successful login |

**User Model Behavior:**
- **Soft Delete:** Users are never hard-deleted; `is_disabled` flag is used instead
- **Auto-Logging:** All CRUD operations logged to AuditLog with old/new values
- **Authentication:** Email-based login with specific failure tracking (logged internally, vague to caller)
- **Verification:** New accounts require admin verification before login
- **Audit Sanitization:** `password_hash` is never logged

#### Resident Model
| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Primary key |
| `full_name` | TEXT | Resident's full name |
| `medical_record_number` | TEXT (UNIQUE) | Unique MRN |

> **Scope Note**: The Resident model includes only essential identification fields. Detailed demographic information (date of birth, room assignment, emergency contacts) is outside the current application scope, which focuses on daily health tracking and medication management.

#### Medication Model (Configuration)
| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Primary key |
| `resident_id` | INTEGER (FK) | → Resident.id |
| `name` | TEXT | Medication name |
| `dosage` | TEXT | Dosage amount |
| `instructions` | TEXT | Admin instructions |
| `morning` | BOOLEAN | Given in morning |
| `afternoon` | BOOLEAN | Given in afternoon |
| `night` | BOOLEAN | Given at night |
| `is_active` | BOOLEAN | Currently prescribed |

#### DailyRecord Model
| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Primary key |
| `resident_id` | INTEGER (FK) | → Resident.id |
| `record_date` | DATE | Date of record |
| `bathing_taken` | BOOLEAN | Was bathed today |
| `comment` | TEXT | Free text for: bathing details, cognitive state, mood, behavior, meals, any other observations |
| **Lock Fields** | | |
| `locked_by` | INTEGER (FK) | User who locked record |
| `locked_at` | DATETIME | When lock was acquired |
| `expires_at` | DATETIME | Lock expiration (1 hour) |

#### MedicationRecord Model
| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Primary key |
| `resident_id` | INTEGER (FK) | → Resident.id |
| `medication_id` | INTEGER (FK) | → Medication.id |
| `record_date` | DATE | Date of administration |
| `time_slot` | TEXT | Morning/Afternoon/Night |
| `status` | TEXT | Taken/Refused/Not Taken |
| `comment` | TEXT | Notes or reason (if not taken/refused) |
| **Lock Fields** | | |
| `locked_by` | INTEGER (FK) | User who locked record |
| `locked_at` | DATETIME | When lock was acquired |
| `expires_at` | DATETIME | Lock expiration (1 hour) |

#### AuditLog Model
| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Primary key |
| `user_id` | INTEGER (FK) | Who made the change (NULL for failed login attempts) |
| `action` | TEXT | CREATE/UPDATE/DELETE/LOGIN/FAILED_LOGIN |
| `table_name` | TEXT | Table affected (NULL for login actions) |
| `record_id` | INTEGER | Specific record (NULL for login actions) |
| `old_values` | TEXT | JSON of previous state |
| `new_values` | TEXT | JSON of new state (includes failure reason for FAILED_LOGIN) |
| `ip_address` | TEXT | Client IP |
| `timestamp` | DATETIME | When change occurred |

---

## Audit Trail & Record Locking

### Audit Trail

> All CRUD operations are tracked in the **AuditLog** model.
> Additionally, **login attempts** (both successful and failed) are logged for security monitoring.
> Audit fields (`created_by`, `created_at`, `updated_by`, `updated_at`) are **not stored directly on individual models** to maintain simplicity.
> Complete history (who, what, when, old values, new values) is available via the AuditLog API.

### Record Locking

Records can be locked at the **individual record level** to prevent concurrent edits:

- **Lock Duration**: 1 hour
- **Lock Fields**: `locked_by`, `locked_at`, `expires_at`
- **Applies To**: DailyRecord, MedicationRecord

**Lock Behavior:**
1. When a user edits a record, it becomes **locked** for 1 hour
2. Other users **cannot modify** the locked record until:
   - Time expires (1 hour passes), OR
   - The locker **unlocks** it manually
3. **Read access** is always allowed, regardless of lock state

**LockService** (Shared utility):
- `checkLock($record)` - Verify if record is locked
- `acquireLock($record, $userId)` - Lock record for 1 hour
- `releaseLock($record)` - Manual unlock
- `isExpired($lock)` - Check if lock has expired

Used by controllers:
- `DailyRecordController`
- `MedicationRecordController`

---

## Model Relationships

```
User (1) ────────< (N) DailyRecord [with lock fields]
     │                  ↑
     │                  │
     └───────< (N) MedicationRecord [with lock fields]
     │                  ↑
     │                  │
Resident (1) ─────< (N) DailyRecord
     │                  │
     ├────────────< (N) Medication ───< (N) MedicationRecord
     │
     └────────────< (N) AuditLog (indirect, via operations)
```

---

## Project Structure

```
health-tracker/
├── docs/
│   ├── PROJECT.md              # This file (consolidated plan)
│   └── archive/                # Historical documents
├── backend/                    # PHP API Backend
│   ├── config/
│   │   ├── database.php       # SQLite connection & encryption
│   │   ├── auth.php           # JWT configuration
│   │   └── encryption.php     # Encryption utilities
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
│   ├── services/
│   │   └── LockService.php    # Shared lock logic
│   ├── middleware/
│   │   ├── AuthMiddleware.php
│   │   └── RBACMiddleware.php
│   ├── utils/
│   │   ├── Response.php
│   │   ├── Validator.php
│   │   ├── Encryption.php
│   │   └── JwtHandler.php     # JWT lifecycle management
|   ├── database/
│   │   └── health_tracker.db
│   └── public/
│       ├── index.php          # API entry point
│       └── .htaccess          # URL rewriting
├── frontend/                  # React Frontend
│   ├── src/
│   │   ├── components/        # Reusable UI components
│   │   ├── pages/             # Route pages
│   │   ├── context/           # React Context (auth, etc.)
│   │   ├── hooks/             # Custom React hooks
│   │   ├── services/          # API service layer
│   │   └── App.jsx
│   ├── public/
│   └── package.json
└── docker-compose.yml         # Optional: Local dev environment
```

---

## Additional Documentation

| Document | Description |
|----------|-------------|
| **API.md** | Complete API endpoint documentation |
| **SCHEMA.md** | Database schema and table definitions |
| **IMPLEMENTATION_SUMMARY.md** | Technical implementation details |

---

## Task Tracking

For the current implementation roadmap and active tasks, please refer to [todo.md](../todo.md).
