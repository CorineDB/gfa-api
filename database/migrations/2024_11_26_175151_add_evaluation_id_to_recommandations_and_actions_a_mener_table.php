<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEvaluationIdToRecommandationsAndActionsAMenerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('recommandations')){
            Schema::table('recommandations', function (Blueprint $table) {

                // Check if the column exists
                if (Schema::hasColumn('recommandations', 'evaluationId')) {
                    // Check for existing foreign key
                    $foreignKey = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'recommandations' 
                        AND COLUMN_NAME = 'evaluationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    ");

                    if (!empty($foreignKey)) {
                        // Drop the foreign key if it exists
                        $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                        $table->dropForeign([$foreignKeyName]);
                    }

                    // Update the column
                    $table->bigInteger('evaluationId')->unsigned()->nullable()->change();

                    // Add the foreign key
                    $table->foreign('evaluationId')->references('id')->on('evaluations_de_gouvernance')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                } else {
                    // Create the column if it doesn't exist
                    $table->bigInteger('evaluationId')->unsigned()->nullable();

                    // Add the foreign key
                    $table->foreign('evaluationId')->references('id')->on('evaluations_de_gouvernance')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if(Schema::hasTable('actions_a_mener')){
            Schema::table('actions_a_mener', function (Blueprint $table) {

                // Check if the column exists
                if (Schema::hasColumn('actions_a_mener', 'evaluationId')) {
                    // Check for existing foreign key
                    $foreignKey = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'actions_a_mener' 
                        AND COLUMN_NAME = 'evaluationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    ");

                    if (!empty($foreignKey)) {
                        // Drop the foreign key if it exists
                        $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                        $table->dropForeign([$foreignKeyName]);
                    }

                    // Update the column
                    $table->bigInteger('evaluationId')->unsigned()->nullable()->change();

                    // Add the foreign key
                    $table->foreign('evaluationId')->references('id')->on('evaluations_de_gouvernance')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                } else {
                    // Create the column if it doesn't exist
                    $table->bigInteger('evaluationId')->unsigned()->nullable();

                    // Add the foreign key
                    $table->foreign('evaluationId')->references('id')->on('evaluations_de_gouvernance')
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
        if(Schema::hasTable('recommandations')){
            Schema::table('recommandations', function (Blueprint $table) {

                if(Schema::hasColumn('recommandations', 'evaluationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'recommandations' 
                        AND COLUMN_NAME = 'evaluationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Drop the foreign key if it exists
                            $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                            $table->dropForeign([$foreignKeyName]);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('evaluationId');
                }

            });
        }

        if(Schema::hasTable('actions_a_mener')){
            Schema::table('actions_a_mener', function (Blueprint $table) {

                if(Schema::hasColumn('actions_a_mener', 'evaluationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'actions_a_mener' 
                        AND COLUMN_NAME = 'evaluationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Drop the foreign key if it exists
                            $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                            $table->dropForeign([$foreignKeyName]);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('evaluationId');
                }

            });
        }
    }
}
