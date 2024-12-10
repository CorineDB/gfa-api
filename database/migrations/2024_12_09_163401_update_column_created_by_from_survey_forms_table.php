<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnCreatedByFromSurveyFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        if(Schema::hasTable('survey_forms')){
            Schema::table('survey_forms', function (Blueprint $table) {

                // Drop polymorphic fields if they exist
                if (Schema::hasColumn('survey_forms', 'created_by_id') && Schema::hasColumn('survey_forms', 'created_by_type')) {
                    try {
                        
                        // Check if the index exists and drop it
                        $indexExists = \DB::select("SHOW INDEX FROM `survey_forms` WHERE Key_name = 'survey_forms_created_by_type_created_by_id_index'");
                        if (!empty($indexExists)) {
                            $table->dropIndex('survey_forms_created_by_type_created_by_id_index');
                        }

                        // Attempt to drop the index
                        //$table->dropIndex(['created_by_id', 'created_by_type']);
                    } catch (\Exception $e) {
                        // Index does not exist, skip dropping
                    }
                
                    // Drop the columns if they exist
                    if (Schema::hasColumn('survey_forms', 'created_by_type')) {
                        // Drop the columns
                        $table->dropColumn('created_by_type');
                    }
    
                    if (Schema::hasColumn('survey_forms', 'created_by_id')) {
                        // Drop the columns
                        $table->dropColumn('created_by_id');
                    }
                }

                // Check if the column exists
                if(Schema::hasColumn('survey_forms', 'created_by')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'survey_forms' 
                        AND COLUMN_NAME = 'created_by' 
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
                            \Log::warning("Foreign key for 'created_by' did not exist or could not be dropped.");
                        }
                    }
                    $table->dropColumn('created_by');
                }

                // Add polymorphic fields
                $table->morphs('created_by');

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

                // Drop polymorphic fields if they exist
                if (Schema::hasColumn('survey_forms', 'created_by_id') && Schema::hasColumn('survey_forms', 'created_by_type')) {
                    try {
                        
                        // Check if the index exists and drop it
                        $indexExists = \DB::select("SHOW INDEX FROM `survey_forms` WHERE Key_name = 'survey_forms_created_by_type_created_by_id_index'");
                        if (!empty($indexExists)) {
                            $table->dropIndex('survey_forms_created_by_type_created_by_id_index');
                        }

                        // Attempt to drop the index
                        //$table->dropIndex(['created_by_id', 'created_by_type']);
                    } catch (\Exception $e) {
                        // Index does not exist, skip dropping
                    }
                
                    // Drop the columns if they exist
                    if (Schema::hasColumn('survey_forms', 'created_by_type')) {
                        // Drop the columns
                        $table->dropColumn('created_by_type');
                    }
    
                    if (Schema::hasColumn('survey_forms', 'created_by_id')) {
                        // Drop the columns
                        $table->dropColumn('created_by_id');
                    }
                }

                // Drop the columns if they exist
                if (!Schema::hasColumn('survey_forms', 'created_by')) {

                    // Re-add 'created_by' as a simple foreign key column
                    $table->unsignedBigInteger('created_by')->nullable();
    
                    // Restore foreign key relationship (update to match your reference table)
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

                }
            });
        }
    }
}
