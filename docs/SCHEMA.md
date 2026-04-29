# Database Schema (Minimal - User Auth Focus)

> Alzheimer's Care Application - XP Phase 1
> Focus: User Authentication and Audit Logging

---

## Table Creation Order

1. `users` (no dependencies)
2. `token_blacklist` (no dependencies)
3. `audit_log` (depends on users)

---

## Users
...
CREATE INDEX idx_users_email ON users(email);

---

## Token Blacklist
-- Table: token_blacklist
-- Purpose: Store revoked JWTs until their natural expiration
CREATE TABLE token_blacklist (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token_hash TEXT UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL
);

-- Index for efficient cleanup and lookups
CREATE INDEX idx_token_blacklist_expires_at ON token_blacklist(expires_at);
CREATE INDEX idx_token_blacklist_hash ON token_blacklist(token_hash);

---

## Audit Log
...
