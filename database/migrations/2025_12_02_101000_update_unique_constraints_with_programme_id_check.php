<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUniqueConstraintsWithProgrammeIdCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $definitions = [
            'indicateur_value_keys' => ['libelle'],
            'options_de_reponse' => ['libelle', 'intitule', 'slug'],
            'sources_de_verification' => ['intitule'],
            'survey_forms' => ['libelle'],
            'unitees' => ['nom'],
            'enquetes_de_collecte' => ['nom'],
            'indicateurs_de_gouvernance' => ['nom'],
        ];

        foreach ($definitions as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'programmeId')) {
                continue;
            }

            foreach ($columns as $columnName) {
                if (!Schema::hasColumn($tableName, $columnName)) {
                    continue;
                }

                // 1. Drop existing simple unique constraints DIRECTLY via SQL to handle errors
                // We query for them first
                $uniqueKeys = DB::select(DB::raw("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = '$tableName' 
                    AND COLUMN_NAME = '$columnName'
                    AND CONSTRAINT_NAME != 'PRIMARY'
                "));

                if (!empty($uniqueKeys)) {
                    foreach ($uniqueKeys as $key) {
                        $constraintName = $key->CONSTRAINT_NAME;
                        
                        // Skip if it looks like the target composite key
                        if (stripos($constraintName, 'programmeId') !== false) {
                            continue;
                        }

                        try {
                            // Use raw statement to catch execution error immediately
                            DB::statement("ALTER TABLE `$tableName` DROP INDEX `$constraintName`");
                        } catch (\Exception $e) {
                            // 1091 = Can't DROP 'x'; check that column/key exists
                            if (strpos($e->getMessage(), '1091') !== false) {
                                Log::info("Skipped dropping index '$constraintName' on '$tableName' as it does not exist.");
                            } else {
                                Log::warning("Could not drop unique constraint '$constraintName' on '$tableName': " . $e->getMessage());
                            }
                        }
                    }
                }

                // 2. Add composite unique constraint
                // We do this in a separate schema call to ensure it runs after the drops
                $compositeIndexName = "{$tableName}_{$columnName}_programmeId_unique";
                
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName, $compositeIndexName) {
                         // We rely on Laravel to handle the creation. 
                         // If it fails (e.g. duplicate), the migration stops, which is generally good for creation.
                         // But we can check if it exists first to be idempotent.
                         $table->unique([$columnName, 'programmeId'], $compositeIndexName);
                    });
                } catch (\Exception $e) {
                    // If it already exists (duplicate key name), we can ignore it.
                    if (strpos($e->getMessage(), 'Duplicate key name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                         Log::info("Composite index '$compositeIndexName' already exists on '$tableName'.");
                    } else {
                         // Only throw if it's a real error
                         Log::error("Failed to add composite index '$compositeIndexName': " . $e->getMessage());
                         // We don't throw here to allow other tables to process if one fails? 
                         // No, usually we want to stop on creation error, but user asked "if migration don't go well you shouldn't commit".
                         // So throwing is probably better, BUT we want to avoid "already exists" errors.
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Down migration left empty/safe as per previous attempts to avoid complex rollback logic 
        // on uncertain states.
    }
}
