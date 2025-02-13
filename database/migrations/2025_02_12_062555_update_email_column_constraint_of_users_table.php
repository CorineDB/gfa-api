<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEmailColumnConstraintOfUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {

                // Check if the column exists
                if (Schema::hasColumn('users', 'email')) {
                    // Query to fetch the unique constraint name for the 'email' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'users' 
                            AND COLUMN_NAME = 'email'
                        "));

                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {

                            // Check if the unique constraint exists
                            if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('users')[$uniqueConstraintName])) {
                                // Drop the unique constraint
                                $table->dropUnique("$uniqueConstraintName");
                            }
                            //$table->dropUnique("users_email_unique");
                            //$table->dropUnique(['email']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        }
                    }

                    // Add the new composite unique constraint on 'email' and 'programmeId'

                    // Check if the composite unique constraint exists
                    if (!isset(\DB::getDoctrineSchemaManager()->listTableIndexes('users')['users_email_programmeid_unique'])) {
                        $table->unique(['email', 'programmeId']);
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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {

                if (Schema::hasColumn('users', 'email')) {
                    // Re-add the unique constraint on the 'intitule' column if needed

                    // Query to fetch the unique constraint name for the 'email' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'users' 
                            AND COLUMN_NAME = 'email'
                        "));
                    // If a unique constraint exists, drop it
                    if (empty($uniqueKey)) {

                        $table->unique('email');
                    }
                }

                // Check if the composite unique constraint exists
                if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('users')['users_email_programmeid_unique'])) {
                    $table->dropUnique(['email', 'programmeId']);
                }
            });
        }
    }
}
