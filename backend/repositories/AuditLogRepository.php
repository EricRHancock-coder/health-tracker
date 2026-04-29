<?php

namespace App\Repositories;

use App\Models\AuditLog;
use App\Utils\Database;

/**
 * AuditLog Repository
 * 
 * Manages persistence for AuditLog entities.
 */
class AuditLogRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Persists a new audit log entry.
     */
    public function save(AuditLog $log): void
    {
        $sql = "INSERT INTO audit_log (
                    user_id, action, table_name, record_id, 
                    old_values, new_values, ip_address, timestamp
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $data = $log->toArray();

        $this->db->execute($sql, [
            $data['user_id'],
            $data['action'],
            $data['table_name'],
            $data['record_id'],
            $data['old_values'],
            $data['new_values'],
            $data['ip_address'],
            $data['timestamp']
        ]);
    }

    /**
     * Retrieves the most recent audit logs.
     */
    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT * FROM audit_log ORDER BY timestamp DESC LIMIT ?";
        $rows = $this->db->query($sql, [$limit]);

        return array_map(fn($row) => new AuditLog($row), $rows);
    }
}
