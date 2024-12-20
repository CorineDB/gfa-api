<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyableColumnsToSurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        if(Schema::hasTable('surveys')){
            Schema::table('surveys', function (Blueprint $table) {

                // Drop polymorphic fields if they exist
                if (Schema::hasColumn('surveys', 'surveyable_id') && Schema::hasColumn('surveys', 'surveyable_type')) {
                    try {
                        
                        // Check if the index exists and drop it
                        $indexExists = \DB::select("SHOW INDEX FROM `surveys` WHERE Key_name = 'surveys_surveyable_type_surveyable_id_index'");
                        if (!empty($indexExists)) {
                            $table->dropIndex('surveys_surveyable_type_surveyable_id_index');
                        }

                        // Attempt to drop the index
                        //$table->dropIndex(['surveyable_id', 'surveyable_type']);
                    } catch (\Exception $e) {
                        // Index does not exist, skip dropping
                    }
                
                    // Drop the columns if they exist
                    if (Schema::hasColumn('surveys', 'surveyable_type')) {
                        // Drop the columns
                        $table->dropColumn('surveyable_type');
                    }
    
                    if (Schema::hasColumn('surveys', 'surveyable_id')) {
                        // Drop the columns
                        $table->dropColumn('surveyable_id');
                    }
                }

                // Check if the column exists
                if(Schema::hasColumn('surveys', 'surveyable')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'surveys' 
                        AND COLUMN_NAME = 'surveyable' 
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
                            \Log::warning("Foreign key for 'surveyable' did not exist or could not be dropped.");
                        }
                    }
                    $table->dropColumn('surveyable');
                }

                // Add polymorphic fields
                $table->nullableMorphs('surveyable');

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

                // Drop polymorphic fields if they exist
                if (Schema::hasColumn('surveys', 'surveyable_id') && Schema::hasColumn('surveys', 'surveyable_type')) {
                    try {
                        
                        // Check if the index exists and drop it
                        $indexExists = \DB::select("SHOW INDEX FROM `surveys` WHERE Key_name = 'surveys_surveyable_type_surveyable_id_index'");
                        if (!empty($indexExists)) {
                            $table->dropIndex('surveys_surveyable_type_surveyable_id_index');
                        }

                        // Attempt to drop the index
                        //$table->dropIndex(['surveyable_id', 'surveyable_type']);
                    } catch (\Exception $e) {
                        // Index does not exist, skip dropping
                    }
                
                    // Drop the columns if they exist
                    if (Schema::hasColumn('surveys', 'surveyable_type')) {
                        // Drop the columns
                        $table->dropColumn('surveyable_type');
                    }
    
                    if (Schema::hasColumn('surveys', 'surveyable_id')) {
                        // Drop the columns
                        $table->dropColumn('surveyable_id');
                    }
                }

                // Drop the columns if they exist
                if (!Schema::hasColumn('surveys', 'surveyable')) {

                    // Re-add 'surveyable' as a simple foreign key column
                    $table->unsignedBigInteger('surveyable')->nullable();
    
                    // Restore foreign key relationship (update to match your reference table)
                    $table->foreign('surveyable')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }
}
