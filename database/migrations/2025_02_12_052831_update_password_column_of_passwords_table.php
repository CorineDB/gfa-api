<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePasswordColumnOfPasswordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('passwords')) {
            Schema::table('passwords', function (Blueprint $table) {

                // Check if the column exists
                if (Schema::hasColumn('passwords', 'password')) {
                    $table->string('password')->nullable()->change();
                    // Query to fetch the unique constraint name for the 'password' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'passwords' 
                            AND COLUMN_NAME = 'password'
                        "));

                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {

                            // Check if the unique constraint exists
                            if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('passwords')[$uniqueConstraintName])) {
                                // Drop the unique constraint
                                $table->dropUnique("$uniqueConstraintName");
                            }
                            //$table->dropUnique("passwords_password_unique");
                            //$table->dropUnique(['password']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        }
                    }

                    // Add the new composite unique constraint on 'password' and 'userId'

                    // Check if the composite unique constraint exists
                    if (!isset(\DB::getDoctrineSchemaManager()->listTableIndexes('passwords')['passwords_password_userid_unique'])) {
                        $table->unique(['password', 'userId']);
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
        if (Schema::hasTable('passwords')) {
            Schema::table('passwords', function (Blueprint $table) {

                if (Schema::hasColumn('passwords', 'password')) {
                    $table->string('password')->nullable(false)->change();
                    // Re-add the unique constraint on the 'intitule' column if needed

                    // Query to fetch the unique constraint name for the 'password' column
                    $uniqueKey = \DB::select(\DB::raw("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'passwords' 
                            AND COLUMN_NAME = 'password'
                        "));
                    // If a unique constraint exists, drop it
                    if (empty($uniqueKey)) {

                        $table->unique('password');
                    }
                }

                // Check if the composite unique constraint exists
                if (isset(\DB::getDoctrineSchemaManager()->listTableIndexes('passwords')['passwords_password_userid_unique'])) {
                    $table->dropUnique(['password', 'userId']);
                }
            });
        }
    }
}
