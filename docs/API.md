# API Documentation

## Authentication Endpoints

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/auth/login | POST | Public | JWT login |
| /api/auth/logout | POST | Auth | Logout |
| /api/auth/me | GET | Auth | Current user info |

## User Management

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/users | GET | Admin | List users |
| /api/users | POST | Admin | Create user |
| /api/users/:id | PUT | Admin | Update user |
| /api/users/:id | DELETE | Admin | Delete user |

## Resident Management

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/residents | GET | Auth | List residents |
| /api/residents | POST | Admin | Create resident |
| /api/residents/:id | GET | Auth | Get resident details |
| /api/residents/:id | PUT | Admin | Update resident |
| /api/residents/:id | DELETE | Admin | Delete resident |

## Medication Management

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/residents/:id/medications | GET | Auth | List medications |
| /api/residents/:id/medications | POST | Admin | Add medication |
| /api/residents/:id/medications/:medId | PUT | Admin | Update medication |
| /api/residents/:id/medications/:medId | DELETE | Admin | Delete medication |

## Daily Records

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/residents/:id/daily-records | GET | Auth | Get daily records |
| /api/residents/:id/daily-records | POST | ReadWrite | Submit daily record |

## Medication Records

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/residents/:id/medication-records | POST | ReadWrite | Submit medication record |

## Audit

| Endpoint | Method | Access | Description |
|----------|--------|--------|-------------|
| /api/audit-log | GET | Admin | View audit log |

## Access Levels

- **Public**: No authentication required
- **Auth**: Any authenticated user
- **ReadWrite**: Users with readwrite or admin role
- **Admin**: Users with admin role only
