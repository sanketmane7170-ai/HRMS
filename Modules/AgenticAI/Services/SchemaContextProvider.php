<?php

namespace Modules\AgenticAI\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

//Sanket v2.0 - generates and caches the full DB schema as a compact string so the AI knows the entire data model
class SchemaContextProvider
{
    //Sanket v2.0 - tables containing infrastructure/sensitive data that the AI should not know about
    protected array $excludedTables = [
        'password_resets', 'password_reset_tokens', 'personal_access_tokens',
        'oauth_access_tokens', 'oauth_refresh_tokens', 'oauth_clients', 'oauth_auth_codes',
        'failed_jobs', 'jobs', 'job_batches', 'sessions', 'cache', 'cache_locks',
        'ai_documents', 'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring',
        'migrations',
    ];

    //Sanket v2.0 - columns to hide from schema description
    protected array $hiddenColumns = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'api_key', 'secret_key', 'token', 'otp', 'fcm_token',
    ];

    //Sanket v2.0 - build compact schema string cached for 24 hours
    public function getSchemaContext(): string
    {
        return Cache::remember('ai_schema_context', 86400, function () {
            return $this->buildSchema();
        });
    }

    //Sanket v2.0 - provide a mapping of table relationships for the AI to understand foreign keys
    public function getRelationshipMap(): string
    {
        return Cache::remember('ai_relationship_map', 86400, function () {
            return $this->buildRelationshipMap();
        });
    }

    //Sanket v2.0 - invalidate cached schema (call after migrations)
    public function invalidate(): void
    {
        Cache::forget('ai_schema_context');
        Cache::forget('ai_relationship_map');
        Cache::forget('ai_schema_table_names');
    }

    //Sanket v2.0 - returns only table names (~500 chars) as a minimal hint for queries that don't need full schema
    public function getTableNamesOnly(): string
    {
        return Cache::remember('ai_schema_table_names', 86400, function () {
            $tables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            $key = "Tables_in_{$dbName}";
            $names = [];
            foreach ($tables as $table) {
                $tableName = $table->$key ?? (array_values((array) $table)[0] ?? null);
                if ($tableName && !in_array($tableName, $this->excludedTables)) {
                    $names[] = $tableName;
                }
            }
            return "Available DB tables: " . implode(', ', $names);
        });
    }

    protected function buildSchema(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        $schema = "=== DATABASE SCHEMA (HR Management System) ===\n";
        $schema .= "Database: {$dbName}\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key ?? (array_values((array)$table)[0] ?? null);
            if (!$tableName || in_array($tableName, $this->excludedTables)) continue;

            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
            $columnDefs = [];

            foreach ($columns as $col) {
                if (in_array($col->Field, $this->hiddenColumns)) continue;

                //Sanket v2.0 - compact format: name(type,key) to minimize token usage
                $def = $col->Field;
                $type = preg_replace('/\(\d+\)/', '', $col->Type); // Remove size like varchar(255) -> varchar
                $markers = [];
                if ($col->Key === 'PRI') $markers[] = 'PK';
                if ($col->Key === 'MUL') $markers[] = 'FK';
                if ($col->Null === 'NO' && $col->Key !== 'PRI') $markers[] = 'req';

                $def .= "({$type}" . (empty($markers) ? '' : ',' . implode(',', $markers)) . ")";
                $columnDefs[] = $def;
            }

            $schema .= "{$tableName}: " . implode(', ', $columnDefs) . "\n";
        }

        return $schema;
    }

    //Sanket v2.0 - auto-detect foreign key relationships from column naming conventions
    protected function buildRelationshipMap(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        $relationships = "=== TABLE RELATIONSHIPS ===\n";

        foreach ($tables as $table) {
            $tableName = $table->$key ?? (array_values((array)$table)[0] ?? null);
            if (!$tableName || in_array($tableName, $this->excludedTables)) continue;

            try {
                $fks = DB::select("
                    SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$dbName, $tableName]);

                foreach ($fks as $fk) {
                    $relationships .= "{$tableName}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
                }
            } catch (\Exception $e) {
                // Skip tables where we can't read FK info
            }
        }

        //Sanket v2.0 - common implicit relationships based on naming convention (user_id -> users.id)
        $relationships .= "\n=== COMMON PATTERNS ===\n";
        $relationships .= "user_id -> users.id (employee who owns the record)\n";
        $relationships .= "created_by -> users.id (who created the record)\n";
        $relationships .= "approved_by -> users.id (who approved it)\n";
        $relationships .= "department_id -> departments.id\n";
        $relationships .= "designation_id -> designations.id\n";
        $relationships .= "branch_id -> branches.id\n";
        $relationships .= "leave_type_id -> leave_types.id\n";

        return $relationships;
    }
}
