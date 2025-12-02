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

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $columnName) {
                    if (!Schema::hasColumn($tableName, $columnName)) {
                        continue;
                    }

                    // 1. Drop existing simple unique constraints
                    // We verify existence before dropping to avoid "Can't DROP INDEX ... check that it exists"
                    
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

                            // Double check via Doctrine schema manager to be absolutely sure it exists
                            // Or just wrap in try-catch and ignore specific error
                            try {
                                $table->dropUnique($constraintName);
                                $droppedConstraints[] = $constraintName;
                            } catch (\Exception $e) {
                                // Log but continue. The index might not exist or other issue.
                                Log::warning("Could not drop unique constraint '$constraintName' on '$tableName': " . $e->getMessage());
                            }
                        }
                    }

                    // Fallback drop by column name if no specific constraints found/dropped
                    // This is where it failed previously: $table->dropUnique([$columnName]) blindly tries to drop 'table_column_unique'
                    // We should ONLY do this if we are sure we haven't already dropped it by name, AND if we suspect it exists.
                    
                    if (empty($droppedConstraints)) {
                         // Instead of blindly dropping, let's check if the default named index exists using Schema Manager
                         $defaultIndexName = "{$tableName}_{$columnName}_unique";
                         $hasIndex = false;
                         
                         try {
                            $sm = Schema::getConnection()->getDoctrineSchemaManager();
                            $indexes = $sm->listTableIndexes($tableName);
                            if(array_key_exists($defaultIndexName, $indexes)) {
                                $hasIndex = true;
                            }
                         } catch (\Exception $e) {
                             // Doctrine not available or failed, ignore
                         }

                         if ($hasIndex) {
                            try {
                                $table->dropUnique([$columnName]);
                            } catch (\Exception $e) {
                                // Ignore
                            }
                         }
                    }

                    // 2. Add composite unique constraint
                    $compositeIndexName = "{$tableName}_{$columnName}_programmeId_unique";
                    
                    try {
                         // Again, check if it exists before adding
                        $sm = Schema::getConnection()->getDoctrineSchemaManager();
                        $indexes = $sm->listTableIndexes($tableName);
                        
                        if (!array_key_exists(strtolower($compositeIndexName), array_change_key_case($indexes, CASE_LOWER))) {
                             $table->unique([$columnName, 'programmeId'], $compositeIndexName);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Could not add composite unique constraint '$compositeIndexName' on '$tableName': " . $e->getMessage());
                    }
                }
            });
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
                        // $table->unique([$columnName]); // Commented out to be safe as per user instruction style
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            });
        }
    }
}