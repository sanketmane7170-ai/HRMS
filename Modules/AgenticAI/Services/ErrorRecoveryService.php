<?php

namespace Modules\AgenticAI\Services;

use Exception;

/**
 * ErrorRecoveryService - Converts technical errors into user-friendly messages
 * with clear recovery paths
 */
class ErrorRecoveryService
{
    /**
     * Error pattern to user-friendly message mapping
     */
    private const ERROR_PATTERNS = [
        // Database errors
        '/Column.*not found/i' => 'Missing data in your profile',
        '/Table.*doesn\'t exist/i' => 'System configuration issue',
        '/Foreign key constraint/i' => 'Related record not found',
        '/Duplicate entry/i' => 'This request already exists',
        '/Data too long/i' => 'Input text is too long',
        
        // Validation errors
        '/required/i' => 'Required information is missing',
        '/invalid/i' => 'Invalid data provided',
        '/must be/i' => 'Data format is incorrect',
        
        // Permission errors
        '/permission/i' => 'You don\'t have permission for this action',
        '/unauthorized/i' => 'Authentication required',
        '/forbidden/i' => 'Access denied',
        
        // Business logic errors
        '/insufficient balance/i' => 'Insufficient leave balance',
        '/already approved/i' => 'This request is already approved',
        '/deadline passed/i' => 'Deadline has passed',
    ];

    /**
     * Recovery suggestions based on error type
     */
    private const RECOVERY_SUGGESTIONS = [
        'Missing data in your profile' => [
            'Contact HR to update your profile',
            'Check if all required fields are filled',
            'Try again after profile update'
        ],
        'Related record not found' => [
            'Verify the ID or reference is correct',
            'Check if the item still exists',
            'Try searching for the item first'
        ],
        'This request already exists' => [
            'Check your existing requests',
            'Cancel the previous request first',
            'Modify the existing request instead'
        ],
        'You don\'t have permission for this action' => [
            'Contact your manager for approval',
            'Check if you have the required role',
            'Request access from HR'
        ],
        'Insufficient leave balance' => [
            'Check your leave balance',
            'Request a different leave type',
            'Reduce the number of days'
        ],
    ];

    /**
     * Handle tool execution failure
     */
    public function handleToolFailure(string $toolName, Exception $error, array $context = []): array
    {
        $userFriendlyMessage = $this->simplifyError($error->getMessage());
        $suggestions = $this->getSuggestions($userFriendlyMessage);
        $alternatives = $this->getAlternatives($toolName, $context);
        
        return [
            'status' => 'FAILED',
            'what_failed' => $this->getUserFriendlyToolName($toolName),
            'why' => $userFriendlyMessage,
            'technical_details' => config('app.debug') ? $error->getMessage() : null,
            'next_steps' => $suggestions,
            'alternatives' => $alternatives,
            'can_retry' => $this->isRetryable($error),
            'support_needed' => $this->needsSupport($error)
        ];
    }

    /**
     * Convert technical error to user-friendly message
     */
    public function simplifyError(string $errorMessage): string
    {
        foreach (self::ERROR_PATTERNS as $pattern => $message) {
            if (preg_match($pattern, $errorMessage)) {
                return $message;
            }
        }
        
        // Default fallback
        return 'A technical issue occurred. Please try again or contact support.';
    }

    /**
     * Get recovery suggestions for an error
     */
    public function getSuggestions(string $userFriendlyError): array
    {
        return self::RECOVERY_SUGGESTIONS[$userFriendlyError] ?? [
            'Try again in a few moments',
            'Check if all information is correct',
            'Contact support if the issue persists'
        ];
    }

    /**
     * Get alternative actions user can take
     */
    private function getAlternatives(string $toolName, array $context): array
    {
        $alternatives = [];
        
        // Tool-specific alternatives
        $alternativeMap = [
            'ApplyLeaveTool' => [
                'Check your leave balance first',
                'View leave policy',
                'Contact your manager'
            ],
            'FileExpenseTool' => [
                'View expense policy',
                'Check previous expense claims',
                'Contact finance team'
            ],
            'SendMailTool' => [
                'Search for the recipient in directory',
                'Use the mail module directly',
                'Send via external email'
            ],
        ];
        
        // Extract base tool name (remove 'Tool' suffix)
        $baseName = str_replace('Tool', '', class_basename($toolName));
        
        foreach ($alternativeMap as $pattern => $alts) {
            if (stripos($baseName, str_replace('Tool', '', $pattern)) !== false) {
                return $alts;
            }
        }
        
        return $alternatives;
    }

    /**
     * Determine if error is retryable
     */
    private function isRetryable(Exception $error): bool
    {
        $retryablePatterns = [
            '/timeout/i',
            '/connection/i',
            '/temporary/i',
            '/try again/i',
        ];
        
        foreach ($retryablePatterns as $pattern) {
            if (preg_match($pattern, $error->getMessage())) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine if error needs support intervention
     */
    private function needsSupport(Exception $error): bool
    {
        $supportNeededPatterns = [
            '/configuration/i',
            '/system/i',
            '/database/i',
            '/server/i',
        ];
        
        foreach ($supportNeededPatterns as $pattern) {
            if (preg_match($pattern, $error->getMessage())) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Convert tool class name to user-friendly name
     */
    private function getUserFriendlyToolName(string $toolName): string
    {
        // Remove namespace and 'Tool' suffix
        $baseName = str_replace('Tool', '', class_basename($toolName));
        
        // Convert CamelCase to words
        $words = preg_split('/(?=[A-Z])/', $baseName, -1, PREG_SPLIT_NO_EMPTY);
        
        return implode(' ', $words);
    }

    /**
     * Format error for logging
     */
    public function formatForLog(string $toolName, Exception $error, array $context): array
    {
        return [
            'tool' => $toolName,
            'error_type' => get_class($error),
            'error_message' => $error->getMessage(),
            'error_code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'context' => $context,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String()
        ];
    }
}
