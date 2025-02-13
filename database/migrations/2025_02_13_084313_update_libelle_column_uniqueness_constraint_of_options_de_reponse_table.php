<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLibelleColumnUniquenessConstraintOfOptionsDeReponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('options_de_reponse')) {
            Schema::table('options_de_reponse', function (Blueprint $table) {

                // Check if the column exists
                if (Schema::hasColumn('options_de_reponse', 'libelle')) {
                    
                    // Query to fetch the unique constraint name for the 'libelle' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'options_de_reponse' 
                            AND COLUMN_NAME = 'libelle'
                        "));

                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {

                            // Check if the unique constraint exists
                            if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('options_de_reponse')[$uniqueConstraintName])) {
                                // Drop the unique constraint
                                $table->dropUnique("$uniqueConstraintName");
                            }
                            //$table->dropUnique("options_de_reponse_libelle_unique");
                            //$table->dropUnique(['libelle']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        }
                    }

                    // Add the new composite unique constraint on 'libelle' and 'programmeId'

                    // Check if the composite unique constraint exists
                    if (!isset(\DB::getDoctrineSchemaManager()->listTableIndexes('options_de_reponse')['options_de_reponse_libelle_programmeid_unique'])) {
                        $table->unique(['libelle', 'programmeId']);
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
        if (Schema::hasTable('options_de_reponse')) {
            Schema::table('options_de_reponse', function (Blueprint $table) {

                if (Schema::hasColumn('options_de_reponse', 'libelle')) {
                    $table->string('libelle')->nullable(false)->change();
                    // Re-add the unique constraint on the 'intitule' column if needed

                    // Query to fetch the unique constraint name for the 'libelle' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'options_de_reponse' 
                            AND COLUMN_NAME = 'libelle'
                        "));
                    // If a unique constraint exists, drop it
                    if (empty($uniqueKey)) {

                        $table->unique('libelle');
                    }
                }

                // Check if the composite unique constraint exists
                if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('options_de_reponse')['options_de_reponse_libelle_programmeid_unique'])) {
                    $table->dropUnique(['libelle', 'programmeId']);
                }
            });
        }
    }
}
