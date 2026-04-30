# Health Tracker Project

## Overview
Alzheimer's Care Application backend.

## Architecture
- Language: PHP 8.2+
- Pattern: Front Controller / API-driven
- Database: RedBeanPHP ORM over SQLite

## Authentication & Security
### JWT Implementation
Currently using stateless JWTs.

### [UNDECIDED] JWT Invalidation Strategy
We need to decide on a strategy for `invalidateServerSideSession()`. 
Options considered:
1. **Token Blacklisting**: Store invalidated tokens in a fast-access cache (e.g., Redis) until they expire.
2. **User-Based Versioning**: Include a `token_version` in the JWT and database. Increment version on logout.
3. **Refresh Token Revocation**: Use short-lived access tokens and long-lived refresh tokens. Revoke the refresh token on logout.
