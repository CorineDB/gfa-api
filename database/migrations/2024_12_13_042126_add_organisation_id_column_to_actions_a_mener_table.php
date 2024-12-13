<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganisationIdColumnToActionsAMenerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('actions_a_mener')){
            Schema::table('actions_a_mener', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('actions_a_mener', 'organisationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'actions_a_mener' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Drop the foreign key if it exists
                            $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                            
                            $table->dropForeign($foreignKeyName);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                            // Log or handle the exception if needed
                            \Log::warning("Foreign key for 'respond_by' did not exist or could not be dropped.");
                        }
                    }
                    $table->dropColumn('organisationId');
                }

                if(!Schema::hasColumn('actions_a_mener', 'organisationId')){
                    $table->bigInteger('organisationId')->unsigned()->nullable();
                    $table->foreign('organisationId')->references('id')->on('organisations')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
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
        if (Schema::hasTable('actions_a_mener')) {
            Schema::table('actions_a_mener', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('actions_a_mener', 'organisationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'actions_a_mener' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Drop the foreign key if it exists
                            $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                            
                            $table->dropForeign($foreignKeyName);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                            // Log or handle the exception if needed
                            \Log::warning("Foreign key for 'respond_by' did not exist or could not be dropped.");
                        }
                    }
                    $table->dropColumn('organisationId');
                }
            });
        }
    }
}
