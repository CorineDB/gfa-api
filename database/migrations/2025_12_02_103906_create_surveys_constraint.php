<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CreateSurveysConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('surveys')) {
            Schema::table('surveys', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('surveys');
                $indexes = array_change_key_case($indexes, CASE_LOWER);

                // 1. Drop the previous composite unique constraint if it exists
                $indexName1 = 'surveys_libelle_programmeId_unique';
                if (array_key_exists(strtolower($indexName1), $indexes)) {
                    $table->dropUnique($indexName1);
                }

                // Also try dropping the simple unique constraint if it exists
                $indexName2 = 'surveys_libelle_unique';
                if (array_key_exists(strtolower($indexName2), $indexes)) {
                    $table->dropUnique($indexName2);
                }

                // 2. Add the new composite unique constraint including created_by fields
                // Unique on: libelle, programmeId, surveyable_type, surveyable_id
                $newIndexName = 'surveys_lib_prog_survey_unique'; // Shortened name to avoid length limits

                if (!array_key_exists(strtolower($newIndexName), $indexes)) {
                    try {
                        $table->unique(
                            ['libelle', 'programmeId', 'surveyable_type', 'surveyable_id'],
                            $newIndexName
                        );
                    } catch (\Exception $e) {
                        Log::warning("Could not create unique constraint '$newIndexName': " . $e->getMessage());
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
        if (Schema::hasTable('surveys')) {
            Schema::table('surveys', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('surveys');
                $indexes = array_change_key_case($indexes, CASE_LOWER);

                // Drop the new 4-column constraint if exists
                $newIndexName = 'surveys_lib_prog_survey_unique';
                if (array_key_exists(strtolower($newIndexName), $indexes)) {
                    $table->dropUnique($newIndexName);
                }

                // Restore the 2-column constraint (previous state) if not exists
                $oldIndexName = 'surveys_libelle_programmeId_unique';
                if (!array_key_exists(strtolower($oldIndexName), $indexes)) {
                    try {
                        $table->unique(['libelle', 'programmeId'], $oldIndexName);
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            });
        }
    }
}
