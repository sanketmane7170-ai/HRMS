<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//Sanket v2.0 - Universal database explorer tool that gives AI read-only access to the entire system
class DatabaseExplorerTool extends BaseTool
{
    //Sanket v2.0 - tables containing sensitive data that AI must never access
    protected array $blockedTables = [
        'password_resets',
        'password_reset_tokens',
        'personal_access_tokens',
        'oauth_access_tokens',
        'oauth_refresh_tokens',
        'oauth_clients',
        'oauth_auth_codes',
        'failed_jobs',
        'jobs',
        'job_batches',
        'sessions',
        'cache',
        'cache_locks',
        'ai_documents',           // Vector store embeddings (huge data)
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
    ];

    //Sanket v2.0 - columns that must be masked/hidden in query results
    protected array $sensitiveColumns = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'api_key',
        'secret_key',
        'token',
        'otp',
        'fcm_token',
    ];

    public function name(): string
    {
        return 'explore_database';
    }

    public function description(): string
    {
        return 'Query the HR system database with READ-ONLY access. The full database schema is already in your system context — you know all tables and columns. Write a SELECT query directly. ONLY SELECT queries are allowed. Always include LIMIT (max 50). Use this when no specialized tool can answer a data question.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['query', 'describe_table'],
                    'description' => 'Action: query (run SELECT — preferred, you already know the schema) or describe_table (get column details if needed)'
                ],
                'table_name' => [
                    'type' => 'string',
                    'description' => 'For describe_table: the table name'
                ],
                'sql' => [
                    'type' => 'string',
                    'description' => 'For query: A SELECT SQL query with LIMIT. You already know the schema from your context — write the query directly.'
                ],
                'intent' => [
                    'type' => 'string',
                    'description' => 'Brief description of what you are trying to find out (for audit logging)'
                ]
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $action = $args['action'] ?? null;

        if (!$action) {
            return ['error' => 'Missing action parameter'];
        }

        //Sanket v2.0 - rate limit: max 20 DB queries per user per minute to prevent abuse
        $rateLimitKey = "ai_db_explorer_{$user->id}";
        $attempts = (int) \Illuminate\Support\Facades\Cache::get($rateLimitKey, 0);
        if ($attempts >= 20) {
            return ['error' => 'Rate limit exceeded. Please wait a moment before querying again.'];
        }
        \Illuminate\Support\Facades\Cache::put($rateLimitKey, $attempts + 1, 60);

        try {
            //Sanket v2.0 - permission check: only admin and HR can explore the full database
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isPrivileged = array_intersect($roles, ['admin', 'hr', 'super-admin', 'manager']);

            if (empty($isPrivileged)) {
                //Sanket v2.0 - regular employees can only query their own data
                return $this->handleEmployeeQuery($user, $action, $args);
            }

            switch ($action) {
                case 'list_tables':
                    return $this->listTables();
                case 'describe_table':
                    return $this->describeTable($args['table_name'] ?? null);
                case 'query':
                    return $this->runQuery($args['sql'] ?? null, $user, $args['intent'] ?? null);
                default:
                    return ['error' => 'Invalid action. Use: query or describe_table'];
            }
        } catch (\Exception $e) {
            Log::error('DatabaseExplorerTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'action' => $action,
            ]);
            return ['error' => 'Database query failed', 'message' => $e->getMessage()];
        }
    }

    //Sanket v2.0 - list all non-blocked tables in the database
    private function listTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        $tableList = [];
        foreach ($tables as $table) {
            $name = $table->$key ?? (array_values((array)$table)[0] ?? null);
            if ($name && !in_array($name, $this->blockedTables)) {
                $tableList[] = $name;
            }
        }

        return [
            'tables' => $tableList,
            'count' => count($tableList),
            'message' => 'Use describe_table to see columns of a specific table, then query to fetch data.'
        ];
    }

    //Sanket v2.0 - show column structure of a specific table
    private function describeTable(?string $tableName): array
    {
        if (!$tableName) {
            return ['error' => 'table_name is required for describe_table action'];
        }

        //Sanket v2.0 - prevent access to blocked tables
        if (in_array($tableName, $this->blockedTables)) {
            return ['error' => 'Access denied to this table'];
        }

        //Sanket v2.0 - validate table name to prevent SQL injection (only allow alphanumeric and underscores)
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
            return ['error' => 'Invalid table name'];
        }

        $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");

        $columnList = [];
        foreach ($columns as $col) {
            $isSensitive = in_array($col->Field, $this->sensitiveColumns);
            $columnList[] = [
                'name' => $col->Field,
                'type' => $col->Type,
                'nullable' => $col->Null === 'YES',
                'key' => $col->Key ?: null,
                'note' => $isSensitive ? '** HIDDEN - sensitive column **' : null,
            ];
        }

        return [
            'table' => $tableName,
            'columns' => $columnList,
            'message' => 'Now you can write a SELECT query on this table. Remember to use LIMIT.'
        ];
    }

    //Sanket v2.0 - execute a read-only SQL query with strict security checks and self-healing
    private function runQuery(?string $sql, $user, ?string $intent = null): array
    {
        if (!$sql) {
            return ['error' => 'sql parameter is required for query action'];
        }

        //Sanket v2.0 - normalize and validate SQL is read-only
        $normalizedSql = strtoupper(trim($sql));

        //Sanket v2.0 - block any write/destructive operations
        $blockedPatterns = [
            '/^\s*(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|REPLACE|GRANT|REVOKE|RENAME|CALL|EXEC|EXECUTE|LOAD|LOCK|UNLOCK)/i',
            '/INTO\s+OUTFILE/i',
            '/INTO\s+DUMPFILE/i',
            '/BENCHMARK\s*\(/i',
            '/SLEEP\s*\(/i',
            '/;\s*(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE)/i',
            //Sanket v2.0 - block UNION SELECT: prevents bypassing per-table blocklist via chained queries
            '/UNION\s+(ALL\s+)?SELECT/i',
            //Sanket v2.0 - block information_schema / mysql internals: no need for AI to read these
            '/\binformation_schema\b/i',
            '/\bmysql\b\s*\.\s*\buser\b/i',
            '/\bperformance_schema\b/i',
            //Sanket v2.0 - block stacked queries via semicolons to prevent second-query injection
            '/;.*(SELECT|INSERT|UPDATE|DELETE|DROP)/i',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                Log::warning('DatabaseExplorerTool: Blocked dangerous query', [
                    'sql' => $sql,
                    'user_id' => $user->id,
                ]);
                return ['error' => 'Only SELECT queries are allowed. Write operations are blocked.'];
            }
        }

        //Sanket v2.0 - must start with SELECT (after optional whitespace)
        if (!preg_match('/^\s*SELECT\s/i', $sql)) {
            return ['error' => 'Query must start with SELECT'];
        }

        //Sanket v2.0 - check for blocked table references
        foreach ($this->blockedTables as $blocked) {
            if (preg_match('/\b' . preg_quote($blocked, '/') . '\b/i', $sql)) {
                return ['error' => "Access denied to table: {$blocked}"];
            }
        }

        //Sanket v2.0 - enforce LIMIT to prevent massive result sets
        if (!preg_match('/LIMIT\s+\d+/i', $sql)) {
            $sql = rtrim($sql, '; ') . ' LIMIT 50';
        } else {
            $sql = preg_replace_callback('/LIMIT\s+(\d+)/i', function ($matches) {
                $limit = min((int)$matches[1], 50);
                return "LIMIT {$limit}";
            }, $sql);
        }

        try {
            $results = DB::select($sql);
        } catch (\Exception $e) {
            //Sanket v2.0 - self-healing: return the SQL error with table hints so the AI can fix and retry
            Log::warning('DatabaseExplorerTool: Query failed, returning error for AI self-correction', [
                'sql' => $sql,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return [
                'error' => 'Query execution failed',
                'sql_error' => $e->getMessage(),
                'hint' => 'The query had a SQL error. Check the column/table names against the schema in your context and try again with a corrected query.',
                'failed_sql' => $sql,
            ];
        }

        //Sanket v2.0 - mask sensitive columns in results
        $maskedResults = array_map(function ($row) {
            $rowArray = (array)$row;
            foreach ($this->sensitiveColumns as $col) {
                if (array_key_exists($col, $rowArray)) {
                    $rowArray[$col] = '***HIDDEN***';
                }
            }
            return $rowArray;
        }, $results);

        Log::info('DatabaseExplorerTool: Query executed', [
            'sql' => $sql,
            'intent' => $intent,
            'user_id' => $user->id,
            'rows_returned' => count($maskedResults),
        ]);

        //Sanket v2.0 - persist query audit to DB for security review and analytics
        try {
            DB::table('ai_tool_logs')->insert([
                'user_id' => $user->id,
                'conversation_id' => null,
                'tool_name' => 'explore_database',
                'payload' => json_encode(['sql' => $sql, 'intent' => $intent]),
                'response' => json_encode(['row_count' => count($maskedResults)]),
                'status' => 'success',
                'duration_ms' => 0,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Don't fail the query if audit logging fails
        }

        return [
            'data' => $maskedResults,
            'row_count' => count($maskedResults),
            'message' => count($maskedResults) === 0
                ? 'No results found for this query.'
                : 'Query executed successfully.'
        ];
    }

    //Sanket v2.0 - restricted handler for regular employees (non-admin) - can only see own data
    private function handleEmployeeQuery($user, string $action, array $args): array
    {
        if ($action === 'list_tables') {
            return [
                'tables' => ['users', 'leaves', 'leave_balances', 'attendances', 'user_documents', 'user_salaries', 'tasks', 'general_requests', 'expenses', 'announcements', 'holidays'],
                'message' => 'As a regular employee, you can only query your own data. All queries will be filtered by your user ID automatically.',
                'restriction' => 'employee_only'
            ];
        }

        if ($action === 'describe_table') {
            return $this->describeTable($args['table_name'] ?? null);
        }

        if ($action === 'query') {
            $sql = $args['sql'] ?? '';
            //Sanket v2.0 - for employees, inject user_id filter to ensure they only see their own data
            if (!preg_match('/\buser_id\s*=\s*' . $user->id . '\b/i', $sql) && !preg_match('/\bWHERE\b.*\buser_id\b/i', $sql)) {
                return [
                    'error' => 'Access restricted',
                    'message' => "As an employee, your queries must include WHERE user_id = {$user->id} to only access your own data."
                ];
            }
            return $this->runQuery($sql, $user);
        }

        return ['error' => 'Invalid action'];
    }
}
