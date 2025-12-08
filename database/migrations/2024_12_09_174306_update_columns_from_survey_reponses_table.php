<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsFromSurveyReponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('survey_reponses')){
            Schema::table('survey_reponses', function (Blueprint $table) {

                // Drop polymorphic fields if they exist
                if (Schema::hasColumn('survey_reponses', 'survey_reponseable_id') && Schema::hasColumn('survey_reponses', 'survey_reponseable_type')) {
                    try {
                        
                        // Check if the index exists and drop it
                        $indexExists = \DB::select("SHOW INDEX FROM `survey_reponses` WHERE Key_name = 'survey_reponses_survey_reponseable_type_survey_reponseable_id_index'");
                        
                        if (!empty($indexExists)) {
                            $table->dropIndex('survey_reponses_survey_reponseable_type_survey_reponseable_id_index');
                        }

                        // Attempt to drop the index
                        //$table->dropIndex(['created_by_id', 'created_by_type']);
                    } catch (\Exception $e) {
                        // Index does not exist, skip dropping
                    }
                
                    // Drop the columns if they exist
                    if (Schema::hasColumn('survey_reponses', 'survey_reponseable_type')) {
                        // Drop the columns
                        $table->dropColumn('survey_reponseable_type');
                    }
    
                    if (Schema::hasColumn('survey_reponses', 'survey_reponseable_id')) {
                        // Drop the columns
                        $table->dropColumn('survey_reponseable_id');
                    }
                }

                // Check if the column exists
                if(Schema::hasColumn('survey_reponses', 'respond_by')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'survey_reponses' 
                        AND COLUMN_NAME = 'respond_by' 
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
                    $table->dropColumn('respond_by');
                }

                // Check if the column exists
                if(!Schema::hasColumn('survey_reponses', 'statut')){

                    $table->boolean('statut')->default(0);
                }

                // Check if the column exists
                if(!Schema::hasColumn('survey_reponses', 'idParticipant')){
                    $table->string('idParticipant')->nullable();
                }

                $table->bigInteger('programmeId')->unsigned();
                $table->foreign('programmeId')->references('id')->on('programmes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        if (Schema::hasTable('survey_reponses')) {
            Schema::table('survey_reponses', function (Blueprint $table) {

                // Drop polymorphic fields if they exist
                if (Schema::hasColumn('survey_reponses', 'survey_reponseable_id') && Schema::hasColumn('survey_reponses', 'survey_reponseable_type')) {
			        $table->nullableMorphs('survey_reponseable', 'reponseable');
                }

                // Drop the columns if they exist
                if (!Schema::hasColumn('survey_reponses', 'respond_by')) {

                    // Re-add 'respond_by' as a simple foreign key column
                    $table->unsignedBigInteger('respond_by')->nullable();
    
                    // Restore foreign key relationship (update to match your reference table)
                    $table->foreign('respond_by')->references('id')->on('users')->onDelete('cascade');

                }

                // Check if the column exists
                if(Schema::hasColumn('survey_reponses', 'statut')){
                    $table->dropColumn('statut');
                }

                // Check if the column exists
                if(Schema::hasColumn('survey_reponses', 'idParticipant')){
                    $table->dropColumn('idParticipant');
                }


                // Check if the column exists
                if(Schema::hasColumn('survey_reponses', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'survey_reponses' 
                        AND COLUMN_NAME = 'programmeId' 
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
                    $table->dropColumn('programmeId');
                }
            });
        }
    }
}
