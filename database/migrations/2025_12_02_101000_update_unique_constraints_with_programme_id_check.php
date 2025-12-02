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

        $processed = [];

        try {
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

                    // Wrap individual table operations in a closure to ensure we capture state for rollback
                    // However, for DDL in MySQL, we can't transaction this.
                    // We proceed optimistically and track for manual rollback.

                    Schema::table($tableName, function (Blueprint $table) use ($tableName, $columnName, &$processed) {

                        // 1. Drop existing simple unique constraints
                        $uniqueKeys = DB::select(DB::raw("
                            SELECT CONSTRAINT_NAME
                            FROM information_schema.KEY_COLUMN_USAGE
                            WHERE TABLE_NAME = '$tableName'
                            AND COLUMN_NAME = '$columnName'
                            AND CONSTRAINT_NAME != 'PRIMARY'
                        "));

                        $droppedConstraints = [];

                        if (!empty($uniqueKeys)) {
                            foreach ($uniqueKeys as $key) {
                                $constraintName = $key->CONSTRAINT_NAME;

                                if (stripos($constraintName, 'programmeId') !== false) {
                                    continue;
                                }

                                try {
                                    $table->dropUnique($constraintName);
                                    $droppedConstraints[] = $constraintName;
                                } catch (\Exception $e) {
                                    Log::warning("Could not drop unique constraint '$constraintName' on '$tableName': " . $e->getMessage());
                                }
                            }
                        }

                        // Fallback drop by column name if no specific constraints found/dropped
                        if (empty($droppedConstraints)) {
                            try {
                                $table->dropUnique([$columnName]);
                                $droppedConstraints[] = "index on $columnName"; // Marker
                            } catch (\Exception $e) {
                                // Likely didn't exist
                            }
                        }

                        // 2. Add composite unique constraint
                        $compositeIndexName = "{$tableName}_{$columnName}_programmeId_unique";

                        try {
                            $table->unique([$columnName, 'programmeId'], $compositeIndexName);

                            // Record success for potential rollback
                            $processed[] = [
                                'table' => $tableName,
                                'column' => $columnName,
                                'composite_index' => $compositeIndexName,
                                'dropped_simple_constraints' => $droppedConstraints
                            ];

                        } catch (\Exception $e) {
                            // If adding the unique constraint fails, we must throw to trigger the outer catch
                            throw new \Exception("Failed to add composite unique '$compositeIndexName' on '$tableName': " . $e->getMessage());
                        }
                    });
                }
            }
        } catch (\Throwable $e) {
            // Manual Rollback of all successfully processed items
            Log::error("Migration failed. Rolling back changes... Error: " . $e->getMessage());

            foreach (array_reverse($processed) as $item) {
                $tableName = $item['table'];
                $columnName = $item['column'];
                $compositeIndexName = $item['composite_index'];

                Schema::table($tableName, function (Blueprint $table) use ($compositeIndexName, $columnName) {
                    // 1. Drop the composite key we just added
                    try {
                        $table->dropUnique($compositeIndexName);
                    } catch (\Exception $ex) {
                        Log::error("Rollback failed to drop composite '$compositeIndexName': " . $ex->getMessage());
                    }

                    // 2. Re-add the simple unique key (we can't easily restore exact names of dropped constraints if they were auto-generated,
                    // but we can restore the functional constraint on the column)
                    try {
                        //$table->unique([$columnName]);
                    } catch (\Exception $ex) {
                         Log::error("Rollback failed to restore unique on '$columnName': " . $ex->getMessage());
                    }
                });
            }

            throw $e; // Re-throw so the migration command reports failure
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
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

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $columnName) {
                    if (!Schema::hasColumn($tableName, $columnName)) {
                        continue;
                    }

                    // Drop composite
                    $compositeIndexName = "{$tableName}_{$columnName}_programmeId_unique";
                    try {
                        $table->dropUnique($compositeIndexName);
                    } catch (\Exception $e) {
                        // Ignore
                    }

                    // Restore simple unique
                    try {
                        //$table->unique([$columnName]);
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            });
        }
    }
}
