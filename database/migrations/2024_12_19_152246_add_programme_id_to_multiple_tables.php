<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgrammeIdToMultipleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('unitees')) {
            Schema::table('unitees', function (Blueprint $table) {
                if (!Schema::hasColumn('unitees', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }
        if (Schema::hasTable('organisations')) {
            Schema::table('organisations', function (Blueprint $table) {
                if (!Schema::hasColumn('organisations', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }
        if (Schema::hasTable('template_rapports')) {
            Schema::table('template_rapports', function (Blueprint $table) {
                if (!Schema::hasColumn('template_rapports', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('indicateur_valeurs')) {
            Schema::table('indicateur_valeurs', function (Blueprint $table) {
                if (!Schema::hasColumn('indicateur_valeurs', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('indicateur_value_keys')) {
            Schema::table('indicateur_value_keys', function (Blueprint $table) {
                if (!Schema::hasColumn('indicateur_value_keys', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('indicateurs_de_gouvernance')) {
            Schema::table('indicateurs_de_gouvernance', function (Blueprint $table) {
                if (!Schema::hasColumn('indicateurs_de_gouvernance', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('suivis')) {
            Schema::table('suivis', function (Blueprint $table) {
                if (!Schema::hasColumn('suivis', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('audits')) {
            Schema::table('audits', function (Blueprint $table) {
                if (!Schema::hasColumn('audits', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('resultats')) {
            Schema::table('resultats', function (Blueprint $table) {
                if (!Schema::hasColumn('resultats', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('member_teams')) {
            Schema::table('member_teams', function (Blueprint $table) {
                if (!Schema::hasColumn('member_teams', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('rappels')) {
            Schema::table('rappels', function (Blueprint $table) {
                if (!Schema::hasColumn('rappels', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('composantes')) {
            Schema::table('composantes', function (Blueprint $table) {
                if (!Schema::hasColumn('composantes', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('activites')) {
            Schema::table('activites', function (Blueprint $table) {
                if (!Schema::hasColumn('activites', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('taches')) {
            Schema::table('taches', function (Blueprint $table) {
                if (!Schema::hasColumn('taches', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('decaissements')) {
            Schema::table('decaissements', function (Blueprint $table) {
                if (!Schema::hasColumn('decaissements', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('plan_de_decaissements')) {
            Schema::table('plan_de_decaissements', function (Blueprint $table) {
                if (!Schema::hasColumn('plan_de_decaissements', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('email_rapports')) {
            Schema::table('email_rapports', function (Blueprint $table) {
                if (!Schema::hasColumn('email_rapports', 'programmeId')) {
                    $table->bigInteger('programmeId')->unsigned()->nullable();
                    $table->foreign('programmeId')->references('id')->on('programmes')
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
        if (Schema::hasTable('unitees')) {
            Schema::table('unitees', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('unitees', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'unitees' 
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

        if (Schema::hasTable('organisations')) {
            Schema::table('organisations', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('organisations', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'organisations' 
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

        if (Schema::hasTable('template_rapports')) {
            Schema::table('template_rapports', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('template_rapports', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'template_rapports' 
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

        if (Schema::hasTable('indicateur_valeurs')) {
            Schema::table('indicateur_valeurs', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('indicateur_valeurs', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'indicateur_valeurs' 
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

        if (Schema::hasTable('indicateur_value_keys')) {
            Schema::table('indicateur_value_keys', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('indicateur_value_keys', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'indicateur_value_keys' 
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

        if (Schema::hasTable('indicateurs_de_gouvernance')) {
            Schema::table('indicateurs_de_gouvernance', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('indicateurs_de_gouvernance', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'indicateurs_de_gouvernance' 
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

        if (Schema::hasTable('suivis')) {
            Schema::table('suivis', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('suivis', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'suivis' 
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

        if (Schema::hasTable('rappels')) {
            Schema::table('rappels', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('rappels', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'rappels' 
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

        if (Schema::hasTable('resultats')) {
            Schema::table('resultats', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('resultats', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'resultats' 
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

        if (Schema::hasTable('member_teams')) {
            Schema::table('member_teams', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('member_teams', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'member_teams' 
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

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('roles', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'roles' 
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

        if (Schema::hasTable('audits')) {
            Schema::table('audits', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('audits', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'audits' 
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

        if (Schema::hasTable('composantes')) {
            Schema::table('composantes', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('composantes', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'composantes' 
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

        if (Schema::hasTable('activites')) {
            Schema::table('activites', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('activites', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'activites' 
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

        if (Schema::hasTable('taches')) {
            Schema::table('taches', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('taches', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'taches' 
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

        if (Schema::hasTable('decaissements')) {
            Schema::table('decaissements', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('decaissements', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'decaissements' 
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

        if (Schema::hasTable('plan_de_decaissements')) {
            Schema::table('plan_de_decaissements', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('plan_de_decaissements', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'plan_de_decaissements' 
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

        if (Schema::hasTable('email_rapports')) {
            Schema::table('email_rapports', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('email_rapports', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'email_rapports' 
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
