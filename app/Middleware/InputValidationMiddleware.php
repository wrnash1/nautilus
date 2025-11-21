<?php

namespace App\Middleware;

/**
 * Input Validation Middleware
 * Provides centralized input validation and sanitization
 */
class InputValidationMiddleware
{
    private array $errors = [];
    private array $rules = [];

    /**
     * Validate input against rules
     *
     * @param array $data The data to validate
     * @param array $rules Validation rules
     * @return bool True if valid, false otherwise
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->rules = $rules;

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Apply a validation rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleParams = $ruleParts[1] ?? null;

        $fieldLabel = ucfirst(str_replace('_', ' ', $field));

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, "{$fieldLabel} is required");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$fieldLabel} must be a valid email address");
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleParams) {
                    $this->addError($field, "{$fieldLabel} must be at least {$ruleParams} characters");
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleParams) {
                    $this->addError($field, "{$fieldLabel} must not exceed {$ruleParams} characters");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "{$fieldLabel} must be numeric");
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "{$fieldLabel} must be an integer");
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha(str_replace(' ', '', $value))) {
                    $this->addError($field, "{$fieldLabel} must contain only letters");
                }
                break;

            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum(str_replace(' ', '', $value))) {
                    $this->addError($field, "{$fieldLabel} must contain only letters and numbers");
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $date = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        $this->addError($field, "{$fieldLabel} must be a valid date (YYYY-MM-DD)");
                    }
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "{$fieldLabel} must be a valid URL");
                }
                break;

            case 'ip':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_IP)) {
                    $this->addError($field, "{$fieldLabel} must be a valid IP address");
                }
                break;

            case 'regex':
                if (!empty($value) && !preg_match($ruleParams, $value)) {
                    $this->addError($field, "{$fieldLabel} format is invalid");
                }
                break;

            case 'in':
                $validValues = explode(',', $ruleParams);
                if (!empty($value) && !in_array($value, $validValues)) {
                    $this->addError($field, "{$fieldLabel} must be one of: " . implode(', ', $validValues));
                }
                break;

            case 'phone':
                // Basic phone validation - matches most formats
                $phone = preg_replace('/[^0-9]/', '', $value);
                if (!empty($value) && (strlen($phone) < 10 || strlen($phone) > 15)) {
                    $this->addError($field, "{$fieldLabel} must be a valid phone number");
                }
                break;

            case 'credit_card':
                if (!empty($value) && !$this->validateCreditCard($value)) {
                    $this->addError($field, "{$fieldLabel} must be a valid credit card number");
                }
                break;

            case 'postal_code':
                // US and international postal codes
                if (!empty($value) && !preg_match('/^[A-Z0-9\s\-]{3,10}$/i', $value)) {
                    $this->addError($field, "{$fieldLabel} must be a valid postal code");
                }
                break;

            case 'unique':
                // Format: unique:table,column,except_id
                list($table, $column, $exceptId) = array_pad(explode(',', $ruleParams), 3, null);
                if (!empty($value) && $this->exists($table, $column, $value, $exceptId)) {
                    $this->addError($field, "{$fieldLabel} already exists");
                }
                break;

            case 'exists':
                // Format: exists:table,column
                list($table, $column) = explode(',', $ruleParams);
                if (!empty($value) && !$this->exists($table, $column, $value)) {
                    $this->addError($field, "{$fieldLabel} does not exist");
                }
                break;
        }
    }

    /**
     * Add validation error
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Validate credit card using Luhn algorithm
     */
    private function validateCreditCard(string $number): bool
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return ($sum % 10) == 0;
    }

    /**
     * Check if value exists in database
     */
    private function exists(string $table, string $column, $value, $exceptId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $params = [$value];

            if ($exceptId !== null) {
                $sql .= " AND id != ?";
                $params[] = $exceptId;
            }

            $result = \App\Core\Database::fetchOne($sql, $params);
            return ($result['count'] ?? 0) > 0;
        } catch (\Exception $e) {
            error_log("Validation exists check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sanitize input data
     *
     * @param array $data Data to sanitize
     * @param array $rules Sanitization rules
     * @return array Sanitized data
     */
    public function sanitize(array $data, array $rules = []): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $rule = $rules[$key] ?? 'string';

            $sanitized[$key] = match($rule) {
                'email' => filter_var($value, FILTER_SANITIZE_EMAIL),
                'url' => filter_var($value, FILTER_SANITIZE_URL),
                'int', 'integer' => filter_var($value, FILTER_SANITIZE_NUMBER_INT),
                'float' => filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'html' => strip_tags($value),
                'string' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                'raw' => $value,
                default => htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            };
        }

        return $sanitized;
    }

    /**
     * Create validator instance for fluent validation
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self();
        $validator->validate($data, $rules);
        return $validator;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
}
