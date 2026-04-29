<?php

namespace App\Models;

/**
 * AuditLog Model
 * 
 * Records all system activity, including CRUD operations and authentication attempts.
 */
class AuditLog {
    public ?int $id = null;
    public ?int $userId = null;
    public string $action; // CREATE, UPDATE, DELETE, LOGIN, FAILED_LOGIN
    public ?string $tableName = null;
    public ?int $recordId = null;
    public ?string $oldValues = null; // JSON string
    public ?string $newValues = null; // JSON string
    public ?string $ipAddress = null;
    public string $timestamp;

    /**
     * @param array $data Associative array of audit log attributes
     */
    public function __construct(array $data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $this->action = $data['action'];
        $this->tableName = $data['table_name'] ?? null;
        $this->recordId = isset($data['record_id']) ? (int)$data['record_id'] : null;
        $this->oldValues = $data['old_values'] ?? null;
        $this->newValues = $data['new_values'] ?? null;
        $this->ipAddress = $data['ip_address'] ?? null;
        $this->timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    }

    /**
     * Converts the object to an associative array for database insertion/updates.
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'action' => $this->action,
            'table_name' => $this->tableName,
            'record_id' => $this->recordId,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'ip_address' => $this->ipAddress,
            'timestamp' => $this->timestamp,
        ];
    }
}
