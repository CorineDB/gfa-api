<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyFormsConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('survey_forms')) {
            Schema::table('survey_forms', function (Blueprint $table) {
                // 1. Drop the previous composite unique constraint if it exists
                // Based on your SHOW INDEX, the name is 'survey_forms_libelle_programmeId_unique'
                try {
                    $table->dropUnique('survey_forms_libelle_programmeId_unique');
                } catch (\Exception $e) {
                    Log::info("Constraint 'survey_forms_libelle_programmeId_unique' not found or could not be dropped: " . $e->getMessage());
                }

                // Also try dropping the simple unique constraint just in case
                 try {
                    $table->dropUnique('survey_forms_libelle_unique');
                } catch (\Exception $e) {
                     // Ignore
                }

                // 2. Add the new composite unique constraint including created_by fields
                // Unique on: libelle, programmeId, created_by_type, created_by_id
                $newIndexName = 'survey_forms_lib_prog_creator_unique'; // Shortened name to avoid length limits

                try {
                    // Check if index exists before adding
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $indexes = $sm->listTableIndexes('survey_forms');

                    if (!array_key_exists(strtolower($newIndexName), array_change_key_case($indexes, CASE_LOWER))) {
                        $table->unique(
                            ['libelle', 'programmeId', 'created_by_type', 'created_by_id'],
                            $newIndexName
                        );
                    }
                } catch (\Exception $e) {
                     Log::warning("Could not create unique constraint '$newIndexName': " . $e->getMessage());
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
        if (Schema::hasTable('survey_forms')) {
            Schema::table('survey_forms', function (Blueprint $table) {
                // Drop the new 4-column constraint
                try {
                    //$table->dropUnique('survey_forms_lib_prog_creator_unique');
                } catch (\Exception $e) {
                    // Ignore
                }

                // Restore the 2-column constraint (previous state)
                try {
                    //$table->unique(['libelle', 'programmeId'], 'survey_forms_libelle_programmeId_unique');
                } catch (\Exception $e) {
                    // Ignore
                }
            });
        }
    }
}
