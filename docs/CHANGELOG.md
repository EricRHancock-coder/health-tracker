# Changelog

All notable changes to the Health Tracker project.

## [Unreleased]

### Fixed
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
