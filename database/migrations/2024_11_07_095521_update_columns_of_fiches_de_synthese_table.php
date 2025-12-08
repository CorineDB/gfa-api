<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsOfFichesDeSyntheseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable("fiches_de_synthese")){
            Schema::table('fiches_de_synthese', function (Blueprint $table) {
                
                if(Schema::hasColumn('fiches_de_synthese', 'soumissionId')){
                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'fiches_de_synthese' 
                        AND COLUMN_NAME = 'soumissionId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Use the Laravel-generated constraint name
                            $table->dropForeign(['soumissionId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('soumissionId');
                }
            
                if(!Schema::hasColumn('fiches_de_synthese', 'evaluationDeGouvernanceId')){
                    $table->bigInteger('evaluationDeGouvernanceId')->nullable()->unsigned();
                    $table->foreign('evaluationDeGouvernanceId')->references('id')->on('evaluations_de_gouvernance')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

                if(!Schema::hasColumn('fiches_de_synthese', 'organisationId')){
                    $table->bigInteger('organisationId')->nullable()->unsigned();
                    $table->foreign('organisationId')->references('id')->on('organisations')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            
                if(!Schema::hasColumn('fiches_de_synthese', 'formulaireDeGouvernanceId')){
                    $table->bigInteger('formulaireDeGouvernanceId')->nullable()->unsigned();
                    $table->foreign('formulaireDeGouvernanceId')->references('id')->on('formulaires_de_gouvernance')
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
        if(Schema::hasTable('fiches_de_synthese')){
            Schema::table('fiches_de_synthese', function (Blueprint $table) {
                if(!Schema::hasColumn('fiches_de_synthese', 'soumissionId')){
                    $table->bigInteger('soumissionId')->nullable()->unsigned();
                    $table->foreign('soumissionId')->references('id')->on('soumissions')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

                if(Schema::hasColumn('fiches_de_synthese', 'evaluationDeGouvernanceId')){
                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'fiches_de_synthese' 
                        AND COLUMN_NAME = 'evaluationDeGouvernanceId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Use the Laravel-generated constraint name
                            $table->dropForeign(['evaluationDeGouvernanceId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('evaluationDeGouvernanceId');
                }

                if(Schema::hasColumn('fiches_de_synthese', 'organisationId')){
                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'fiches_de_synthese' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Use the Laravel-generated constraint name
                            $table->dropForeign(['organisationId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('organisationId');
                }

                if(Schema::hasColumn('fiches_de_synthese', 'formulaireDeGouvernanceId')){
                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'fiches_de_synthese' 
                        AND COLUMN_NAME = 'formulaireDeGouvernanceId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            // Use the Laravel-generated constraint name
                            $table->dropForeign(['formulaireDeGouvernanceId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('formulaireDeGouvernanceId');
                }
            });
        }
    }
}
