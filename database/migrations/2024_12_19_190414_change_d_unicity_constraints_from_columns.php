<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDUnicityConstraintsFromColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('indicateur_value_keys')){
            
            Schema::table('indicateur_value_keys', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('indicateur_value_keys', 'libelle')){
                    // Query to fetch the unique constraint name for the 'libelle' column
                    $uniqueKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'indicateur_value_keys' 
                        AND COLUMN_NAME = 'libelle'
                    "));
                
                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {
                            // Drop the unique constraint
                            $table->dropUnique("$uniqueConstraintName");
                            //$table->dropUnique("indicateur_value_keys_libelle_unique");
                            //$table->dropUnique(['libelle']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        } 
                    }else {
                        // Fallback: Drop unique constraint using column name
                        $table->dropUnique(['libelle']);
                    }
                }
            });
        }

        if(Schema::hasTable('options_de_reponse')){
            
            Schema::table('options_de_reponse', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('options_de_reponse', 'intitule')){
                    // Query to fetch the unique constraint name for the 'intitule' column
                    $uniqueKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'options_de_reponse' 
                        AND COLUMN_NAME = 'intitule'
                    "));
                
                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {
                            // Drop the unique constraint
                            $table->dropUnique("$uniqueConstraintName");
                            //$table->dropUnique("options_de_reponse_intitule_unique");
                            //$table->dropUnique(['intitule']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        } 
                    }else {
                        // Fallback: Drop unique constraint using column name
                        $table->dropUnique(['intitule']);
                    }
                }
            });
        }

        if(Schema::hasTable('sources_de_verification')){
            
            Schema::table('sources_de_verification', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('sources_de_verification', 'intitule')){
                    // Query to fetch the unique constraint name for the 'intitule' column
                    $uniqueKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'sources_de_verification' 
                        AND COLUMN_NAME = 'intitule'
                    "));
                
                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {
                            // Drop the unique constraint
                            $table->dropUnique("$uniqueConstraintName");
                            //$table->dropUnique("sources_de_verification_intitule_unique");
                            //$table->dropUnique(['intitule']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        } 
                    }else {
                        // Fallback: Drop unique constraint using column name
                        $table->dropUnique(['intitule']);
                    }
                }
            });
        }

        if(Schema::hasTable('survey_forms')){
            
            Schema::table('survey_forms', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('survey_forms', 'libelle')){
                    // Query to fetch the unique constraint name for the 'libelle' column
                    $uniqueKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'survey_forms' 
                        AND COLUMN_NAME = 'libelle'
                    "));
                
                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {
                            // Drop the unique constraint
                            $table->dropUnique("$uniqueConstraintName");
                            //$table->dropUnique("survey_forms_libelle_unique");
                            //$table->dropUnique(['libelle']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        } 
                    }else {
                        // Fallback: Drop unique constraint using column name
                        $table->dropUnique(['libelle']);
                    }
                }
            });
        }

        if(Schema::hasTable('unitees')){
            
            Schema::table('unitees', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('unitees', 'nom')){
                    // Query to fetch the unique constraint name for the 'nom' column
                    $uniqueKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'unitees' 
                        AND COLUMN_NAME = 'nom'
                    "));
                
                    // If a unique constraint exists, drop it
                    if (!empty($uniqueKey)) {

                        $uniqueConstraintName = $uniqueKey[0]->CONSTRAINT_NAME;

                        // Use try-catch to handle potential errors gracefully
                        try {
                            // Drop the unique constraint
                            $table->dropUnique("$uniqueConstraintName");
                            //$table->dropUnique("unitees_nom_unique");
                            //$table->dropUnique(['nom']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Log a warning if the unique constraint couldn't be dropped
                            \Log::warning("Unique constraint '{$uniqueConstraintName}' could not be dropped: " . $e->getMessage());
                        } 
                    }else {
                        // Fallback: Drop unique constraint using column name
                        $table->dropUnique(['nom']);
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
        if(Schema::hasTable('indicateur_value_keys')){
            Schema::table('indicateur_value_keys', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('indicateur_value_keys', 'libelle')){
                    // Re-add the unique constraint on the 'libelle' column if needed
                        $table->unique('libelle');
                    
                }
            });
        }

        if(Schema::hasTable('options_de_reponse')){
            Schema::table('options_de_reponse', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('options_de_reponse', 'intitule')){
                    // Re-add the unique constraint on the 'intitule' column if needed
                        $table->unique('intitule');
                    
                }
            });
        }

        if(Schema::hasTable('sources_de_verification')){
            Schema::table('sources_de_verification', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('sources_de_verification', 'intitule')){
                    // Re-add the unique constraint on the 'intitule' column if needed
                        $table->unique('intitule');
                    
                }
            });
        }
        
        if(Schema::hasTable('survey_forms')){
            Schema::table('survey_forms', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('survey_forms', 'nom_du_fond')){
                    // Re-add the unique constraint on the 'nom_du_fond' column if needed
                        $table->unique('nom_du_fond');
                    
                }
            });
        }
        
        if(Schema::hasTable('survey_forms')){
            Schema::table('survey_forms', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('survey_forms', 'libelle')){
                    // Re-add the unique constraint on the 'libelle' column if needed
                        $table->unique('libelle');
                    
                }
            });
        }
        
        if(Schema::hasTable('unitees')){
            Schema::table('unitees', function (Blueprint $table) {
                // Check if the column exists
                if(Schema::hasColumn('unitees', 'nom')){
                    // Re-add the unique constraint on the 'nom' column if needed
                        $table->unique('nom');
                    
                }
            });
        }
    }
}
