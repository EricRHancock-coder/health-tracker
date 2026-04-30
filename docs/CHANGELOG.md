# Changelog

All notable changes to the Health Tracker project.

## [Unreleased]

### Changed
#### 2026-04-30
- Finished RedBeanPHP migration. `UserRepository`, `AuditLogRepository`, and `BlacklistRepository` now operate on RedBean beans via `R::*` instead of the bespoke PDO `Database` utility.
- `AuthController` and `JwtHandler` accept `OODBBean` user instances; `AuthController::login` now rejects missing credentials with the same generic 401.
- `config/database.php` is idempotent so tests that pre-wire an in-memory connection are not clobbered when production code requires the file.

### Removed
#### 2026-04-30
- Deleted the orphan `App\Models\User` DTO (duplicate of FUSE `Model_Users`).
- Deleted the `App\Models\AuditLog` DTO (audit log entries are now plain `audit_log` beans).
- Deleted `App\Utils\Database` (no remaining callers after the RedBean migration).
- Deleted `backend/archive/verify_db.php` (one-shot smoke script no longer needed).

### Fixed
#### 2026-04-30
- `UserRepository::findByEmail`/`findById` no longer return `null` for every real user. The previous `instanceof Model_Users` check was always false because `R::findOne` returns `OODBBean`, not the FUSE model class.

#### 2026-04-27
- Fixed `ResponseTest.php` error where object was accessed as an array.
- Fixed `AuthControllerTest.php` deprecation warning regarding `setAccessible()`.

### Added

#### 2026-04-24

**Infrastructure**
- `config/database.php` for SQLite connection management
- `utils/Database.php` singleton utility for PDO operations
- `repositories/UserRepository.php` for user data access

**Testing**
- Unit tests for `Database` utility
- Unit tests for `UserRepository`

**Completed Tasks**
- [x] Database configuration and utility implementation and testing
- [x] User Repository implementation and testing

### Changed

#### 2026-04-24
- Updated JWT expiration to 24 hours in `config/auth.php`
- Updated `docs/PROJECT.md` to reflect temporary authentication decisions

**Utilities**
- `Response` utility for standardized API output
- `Validator` utility for centralized input validation

**Models**
- `User` model (implemented and tested)
- `AuditLog` model (implemented and tested)

**Infrastructure**
- Composer environment initialized with `firebase/php-jwt`
- PHPUnit testing environment set up
- `backend/database/health_tracker.db` initialized with `users` and `audit_log` tables

**Testing**
- Unit tests for `User` model
- Unit tests for `AuditLog` model
- Unit tests for `Response` utility
- Unit tests for `Validator` utility


### Changed

#### 2026-04-23
- Replaced username-based login with email-based authentication
- Removed hard delete in favor of soft delete (is_disabled flag)
- Simplified Resident model (removed demographics - out of scope)
- Audit trail stored in JSON instead of per-model audit fields

### Removed

#### 2026-04-23
- `username` field (replaced by `email`)
- Hard delete functionality for users
- Record locks separate table (moved to lock fields on records)
- Resident demographics fields (DOB, room, emergency contact)

---

## Release Notes Format

### Types of Changes
- **Added** - New features
- **Changed** - Changes to existing functionality
- **Deprecated** - Soon-to-be removed features
- **Removed** - Removed features
- **Fixed** - Bug fixes
- **Security** - Security improvements

---

*Last Updated: 2026-04-27*
