<?php

class ErrorHandler {
    // Error types
    const DB_ERROR = 'database';
    const VALIDATION_ERROR = 'validation';
    const SYSTEM_ERROR = 'system';
    const NETWORK_ERROR = 'network';
    const AUTH_ERROR = 'authentication';
    const SMS_ERROR = 'sms';
    const USSD_ERROR = 'ussd';
    
    // USSD response types
    const CONTINUE = 'CON';
    const END = 'END';
    
    /**
     * Log error and return formatted USSD response
     */
    public static function handleError($type, $message, $shouldEnd = false, $logDetails = '') {
        // Log the error with details
        self::logError($type, $message, $logDetails);
        
        // Format user-friendly message
        $userMessage = self::getUserFriendlyMessage($type, $message);
        
        // Return USSD formatted response
        return self::formatUSSDResponse($userMessage, $shouldEnd);
    }
    
    /**
     * Log error to file with timestamp and details
     */
    private static function logError($type, $message, $details = '') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$type}: {$message}";
        if ($details) {
            $logMessage .= " | Details: {$details}";
        }
        
        error_log($logMessage . PHP_EOL, 3, dirname(__DIR__) . '/logs/ussd_errors.log');
    }
    
    /**
     * Get user-friendly error message based on error type
     */
    private static function getUserFriendlyMessage($type, $message) {
        switch ($type) {
            case self::DB_ERROR:
                return "Sorry, we're having trouble accessing our database. Please try again later.\n\n0. Back to main menu";
                
            case self::VALIDATION_ERROR:
                return "Invalid input: {$message}\n\n0. Back to main menu";
                
            case self::SYSTEM_ERROR:
                return "Sorry, we're experiencing technical difficulties. Please try again later.\n\n0. Back to main menu";
                
            case self::NETWORK_ERROR:
                return "Network error. Please check your connection and try again.\n\n0. Back to main menu";
                
            case self::AUTH_ERROR:
                return "Authentication failed. {$message}\n\n0. Back to main menu";
                
            case self::SMS_ERROR:
                return "Unable to process SMS at the moment. Please try again later.\n\n0. Back to main menu";
                
            case self::USSD_ERROR:
                return "USSD service error. Please try again.\n\n0. Back to main menu";
                
            default:
                return "An error occurred. Please try again later.\n\n0. Back to main menu";
        }
    }
    
    /**
     * Format response for USSD
     */
    private static function formatUSSDResponse($message, $shouldEnd = false) {
        $prefix = $shouldEnd ? self::END : self::CONTINUE;
        return $prefix . " " . $message;
    }
    
    /**
     * Validate USSD required parameters
     */
    public static function validateUSSDParams($params) {
        $required = ['sessionId', 'serviceCode', 'phoneNumber', 'text'];
        $missing = [];
        
        foreach ($required as $param) {
            if (!isset($params[$param]) || empty(trim($params[$param]))) {
                $missing[] = $param;
            }

        }
        
        if (!empty($missing)) {
            return self::handleError(
                self::USSD_ERROR,
                "Missing required parameters: " . implode(', ', $missing),
                true,
                "Missing USSD params: " . implode(', ', $missing)
            );
        }
        
        return true;
    }
    
    /**
     * Handle database connection errors
     */
    public static function handleDBError($exception, $operation = '') {
        return self::handleError(
            self::DB_ERROR,
            "Database operation failed",
            true,
            "Operation: {$operation}, Error: {$exception->getMessage()}"
        );
    }
    
    /**
     * Validate phone number format
     */
    public static function validatePhone($phone) {
        $phone = trim($phone);
        if (empty($phone)) {
            return self::handleError(
                self::VALIDATION_ERROR,
                "Phone number is required",
                false
            );
        }
        
        // Remove any spaces or special characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Format for Kenyan numbers
        if (substr($phone, 0, 1) === '0') {
            $phone = '+254' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }
        
        // Validate format
        if (!preg_match('/^\+254[17]\d{8}$/', $phone)) {
            return self::handleError(
                self::VALIDATION_ERROR,
                "Invalid phone number format",
                false
            );
        }
        
        return $phone;
    }
}
?> 
?> 