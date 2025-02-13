<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateKeyColumnUniquenessConstraintOfIndicateurValueKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('indicateur_value_keys')) {
            Schema::table('indicateur_value_keys', function (Blueprint $table) {

                // Check if the column exists
                if (Schema::hasColumn('indicateur_value_keys', 'key')) {
                    
                    // Query to fetch the unique constraint name for the 'key' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'indicateur_value_keys' 
                            AND COLUMN_NAME = 'key'
                        "));

                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {

                            // Check if the unique constraint exists
                            if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('indicateur_value_keys')[$uniqueConstraintName])) {
                                // Drop the unique constraint
                                $table->dropUnique("$uniqueConstraintName");
                            }
                            //$table->dropUnique("indicateur_value_keys_key_unique");
                            //$table->dropUnique(['key']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        }
                    }

                    // Add the new composite unique constraint on 'key' and 'programmeId'

                    // Check if the composite unique constraint exists
                    if (!isset(\DB::getDoctrineSchemaManager()->listTableIndexes('indicateur_value_keys')['indicateur_value_keys_key_programmeid_unique'])) {
                        $table->unique(['key', 'programmeId']);
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
        if (Schema::hasTable('indicateur_value_keys')) {
            Schema::table('indicateur_value_keys', function (Blueprint $table) {

                if (Schema::hasColumn('indicateur_value_keys', 'key')) {
                    // Re-add the unique constraint on the 'intitule' column if needed

                    // Query to fetch the unique constraint name for the 'key' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'indicateur_value_keys' 
                            AND COLUMN_NAME = 'key'
                        "));
                    // If a unique constraint exists, drop it
                    if (empty($uniqueKey)) {

                        $table->unique('key');
                    }
                }

                // Check if the composite unique constraint exists
                if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('indicateur_value_keys')['indicateur_value_keys_key_programmeid_unique'])) {
                    $table->dropUnique(['key', 'programmeId']);
                }
            });
        }
    }
}
