<?php

namespace Modules\AgenticAI\Services;

class BaseService
{
    /**
     * Standard response format for services.
     */
    protected function success(mixed $data = null, string $message = 'Success'): array
    {
        return [
            'status' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    protected function error(string $message, int $code = 400): array
    {
        return [
            'status' => false,
            'message' => $message,
            'code' => $code,
        ];
    }
}
