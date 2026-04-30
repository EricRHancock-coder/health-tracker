<?php

namespace App\Repositories;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * AuditLog Repository
 *
 * Manages persistence for audit log entries (CRUD activity and login attempts).
 */
class AuditLogRepository
{
    /**
     * Persist an audit log bean. Returns the new row id.
     */
    public function save(OODBBean $log): int
    {
        return (int) R::store($log);
    }

    /**
     * Retrieve the most recent audit log entries, newest first.
     *
     * @return OODBBean[]
     */
    public function getRecent(int $limit = 50): array
    {
        return R::findAll('audit_log', 'ORDER BY timestamp DESC LIMIT ?', [$limit]);
    }
}
