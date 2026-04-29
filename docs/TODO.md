# Health Tracker - TODO

> Alzheimer's Care Application

---

## Backend

### RedBeanPHP Migration
- [ ] Install RedBeanPHP via Composer
- [ ] Initialize RedBeanPHP in `backend/config/database.php`
- [ ] Refactor Models to use RedBeanPHP beans
- [ ] Update Repositories to use RedBeanPHP

### Authentication Implementation (High Priority)
- [x] `config/database.php`
- [x] `utils/Database.php`
- [x] `middleware/AuthMiddleware.php`
- [ ] `middleware/RBACMiddleware.php`
- [x] `repositories/UserRepository.php`
- [x] `repositories/BlacklistRepository.php`
- [x] `controllers/AuthController.php`
- [x] `utils/JwtHandler.php`
- [x] `tests/Auth/AuthControllerTest.php`
- [x] `tests/Utils/JwtHandlerTest.php`
- [x] `tests/Repositories/BlacklistRepositoryTest.php`
- [x] Implement JWT invalidation strategy (Token Blacklisting)
- [ ] `public/index.php` (Routing)

### Models
- [x] `User` - Auto-logging, soft-delete, authentication
- [x] `AuditLog` - Activity tracking with login monitoring
- [ ] `Resident` - Basic CRUD
...
### Utils
- [x] `Response` - Standardized JSON responses
- [x] `Validator` - Input validation
- [x] `Database` - PDO connection & query management
- [x] `JwtHandler` - JWT lifecycle management
- [x] `AuthControllerTest` - Unit tests for authentication
- [x] `JwtHandlerTest` - Unit tests for JWT verification
- [ ] Request IP extraction for audit logging
...
## Completed ✓

### 2026-04-29
- [x] Perform architectural review: confirm stack alignment for low cognitive load (Vite, Axios, RedBeanPHP, etc.)


### 2026-04-27
- [x] Implement `AuthController` with secure error handling and audit logging
- [x] Implement `JwtHandler` utility
- [x] Document undecided JWT invalidation strategy in `PROJECT.md`
- [x] Fix `ResponseTest` array access errors
- [x] Fix `AuthControllerTest` deprecation warnings

---

*Last Updated: 2026-04-28*
