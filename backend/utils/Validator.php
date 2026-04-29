<?php

namespace App\Utils;

/**
 * Validator Utility
 * 
 * Provides centralized input validation logic.
 */
class Validator {
    /**
     * Validates that all required fields are present and not empty.
     * 
     * @param array $data The input data to validate.
     * @param array $fields List of required field names.
     * @return bool True if all fields are present and non-empty.
     */
    public static function validateRequired(array $data, array $fields): bool {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates an email address.
     * 
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates that the provided role is allowed.
     * 
     * @param string $role
     * @return bool
     */
    public static function validateRole(string $role): bool {
        return in_array($role, ['admin', 'readwrite', 'readonly']);
    }
}
