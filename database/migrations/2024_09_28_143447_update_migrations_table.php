<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('programmes')){
            Schema::table('programmes', function (Blueprint $table) {
                if(Schema::hasColumn('programmes', 'budgetNational')){
                    $table->bigInteger('budgetNational')->nullable()->change();
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'pret')){
                    $table->bigInteger('pret')->nullable()->change();
                }

                if(Schema::hasColumn('activites', 'tepPrevu')){
                    $table->bigInteger('tepPrevu')->nullable()->change();
                }

                if(Schema::hasColumn('activites', 'userId')){
                    $table->bigInteger('userId')->unsigned()->nullable()->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('bailleurs')){
            Schema::table('bailleurs', function (Blueprint $table) {
                if(Schema::hasColumn('bailleurs', 'pays')){
                    $table->string('pays')->nullable()->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                
                if(Schema::hasColumn('composantes', 'poids')){
                    $table->integer('poids')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('composantes', 'pret')){
                    $table->integer('pret')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('composantes', 'budgetNational')){
                    $table->integer('budgetNational')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('composantes', 'tepPrevu')){
                    $table->integer('tepPrevu')->nullable()->default(0)->change();
                }

            });
        }

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs', 'valeurDeBase')){
                    $table->json('valeurDeBase')->nullable()->default(null)->change();
                }

                if(Schema::hasColumn('indicateurs', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->nullable()->default(null)->change();
                }

                if(!(Schema::hasColumn('indicateurs', 'indicateurable_id') && Schema::hasColumn('indicateurs', 'indicateurable_type'))){
                    $table->morphs('indicateurable');
                }

                if(Schema::hasColumn('indicateurs', 'uniteeMesureId')){
                    $table->bigInteger('uniteeMesureId')->unsigned()->nullable()->default(null)->change();
                }

                if(!Schema::hasColumn('indicateurs', 'type_de_variable')){
                    $table->enum('type_de_variable', ["quantitatif", "qualitatif", "dichotomique"]);
                }


            });
        }

        if(Schema::hasTable('projets')){
            Schema::table('projets', function (Blueprint $table) {

                if(Schema::hasColumn('projets', 'pret')){
                    $table->bigInteger('pret')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('projets', 'budgetNational')){
                    $table->bigInteger('budgetNational')->nullable()->default(0)->change();
                }

                if(Schema::hasColumn('projets', 'ville')){
                    $table->string('ville')->nullable()->default(null)->change();
                }

                if(Schema::hasColumn('projets', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->nullable()->default(null)->change();
                }

                if(!(Schema::hasColumn('projets', 'projetable_id') && Schema::hasColumn('projets', 'projetable_type'))){
                    $table->morphs('projetable');
                }

            });
        }

        if(Schema::hasTable('projets')){
            Schema::table('projets', function (Blueprint $table) {

                if(Schema::hasColumn('projets', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->nullable()->default(null)->change();
                }


            });
        }
        
        if (Schema::hasTable('reponses_collecter')) {
            Schema::table('reponses_collecter', function (Blueprint $table) {

                if(Schema::hasColumn('reponses_collecter', 'organisationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'reponses_collecter' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['organisationId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
            
                    // Alter the column and set a new foreign key to organisations
                    $table->bigInteger('organisationId')->unsigned()->nullable()->change();

                    // Recreate the foreign key
                    $table->foreign('organisationId')->references('id')->on('organisations')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
                else{
                    // Alter the column and set a new foreign key to organisations
                    $table->bigInteger('organisationId')->unsigned();
                    $table->foreign('organisationId')->references('id')->on('organisations')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
            });
        }
        
        if (Schema::hasTable('enquete_resultat_notes')) {
            Schema::table('enquete_resultat_notes', function (Blueprint $table) {

                if(Schema::hasColumn('enquete_resultat_notes', 'organisationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'enquete_resultat_notes' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['organisationId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
            
                    // Alter the column and set a new foreign key to organisations
                    $table->bigInteger('organisationId')->unsigned()->nullable()->change();

                    // Recreate the foreign key
                    $table->foreign('organisationId')->references('id')->on('organisations')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
                else{
                    // Alter the column and set a new foreign key to organisations
                    $table->bigInteger('organisationId')->unsigned();
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
        if(Schema::hasTable('programmes')){
            Schema::table('programmes', function (Blueprint $table) {
                if(Schema::hasColumn('programmes', 'budgetNational')){
                    $table->bigInteger('budgetNational')->default(0)->change();
                    
                }
            });
        }

        if(Schema::hasTable('activites')){
            Schema::table('activites', function (Blueprint $table) {
                if(Schema::hasColumn('activites', 'pret')){
                    $table->bigInteger('pret')->nullable(false)->change();
                }
                if(Schema::hasColumn('activites', 'tepPrevu')){
                    $table->bigInteger('tepPrevu')->nullable(false)->change();
                }

                if(Schema::hasColumn('activites', 'userId')){
                    $table->bigInteger('userId')->unsigned()->nullable(false)->change();
                }
            });
        }

        if(Schema::hasTable('bailleurs')){
            Schema::table('bailleurs', function (Blueprint $table) {
                if(Schema::hasColumn('bailleurs', 'pays')){
                    $table->string('pays')->nullable(false)->default(null)->change();
                }
            });
        }

        if(Schema::hasTable('composantes')){
            Schema::table('composantes', function (Blueprint $table) {
                
                if(Schema::hasColumn('composantes', 'poids')){
                    $table->integer('poids')->nullable(false)->change();
                }

                if(Schema::hasColumn('composantes', 'pret')){
                    $table->integer('pret')->nullable(false)->change();
                }

                if(Schema::hasColumn('composantes', 'budgetNational')){
                    $table->integer('budgetNational')->nullable(false)->change();
                }

                if(Schema::hasColumn('composantes', 'tepPrevu')){
                    $table->integer('tepPrevu')->nullable(false)->change();
                }

            });
        }

        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs', 'valeurDeBase')){
                    $table->integer('valeurDeBase')->change();
                }

                if(Schema::hasColumn('indicateurs', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->change();
                }

                if((Schema::hasColumn('indicateurs', 'indicateurable_id') && Schema::hasColumn('indicateurs', 'indicateurable_type'))){
                    $table->dropColumn(["indicateurable_id", "indicateurable_type"]);
                }

                if(Schema::hasColumn('indicateurs', 'uniteeMesureId')){
                    $table->bigInteger('uniteeMesureId')->unsigned()->change();
                }

                if(Schema::hasColumn('indicateurs', 'type_de_variable')){
                    $table->dropColumn("type_de_variable");
                }

            });
        }

        if(Schema::hasTable('projets')){
            Schema::table('projets', function (Blueprint $table) {

                if(Schema::hasColumn('projets', 'pret')){
                    $table->bigInteger('pret')->change();
                }

                if(Schema::hasColumn('projets', 'budgetNational')){
                    $table->bigInteger('budgetNational')->change();
                }

                if(Schema::hasColumn('projets', 'ville')){
                    $table->string('ville')->change();
                }

                if(Schema::hasColumn('projets', 'bailleurId')){
                    $table->bigInteger('bailleurId')->unsigned()->change();
                }

                if((Schema::hasColumn('projets', 'projetable_id') && Schema::hasColumn('projets', 'projetable_type'))){
                    $table->dropColumn(["projetable_id", "projetable_type"]);
                }

            });
        }

        if(Schema::hasTable('reponses_collecter')){
            Schema::table('reponses_collecter', function (Blueprint $table) {

                if(Schema::hasColumn('reponses_collecter', 'organisationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'reponses_collecter' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['organisationId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }

                    // Recreate the original foreign key with entreprise_executants
                    $table->foreign('organisationId')->references('id')->on('entreprise_executants')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }

        if(Schema::hasTable('enquete_resultat_notes')){
            Schema::table('enquete_resultat_notes', function (Blueprint $table) {

                if(Schema::hasColumn('enquete_resultat_notes', 'organisationId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'enquete_resultat_notes' 
                        AND COLUMN_NAME = 'organisationId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['organisationId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }

                    // Recreate the original foreign key with entreprise_executants
                    $table->foreign('organisationId')->references('id')->on('entreprise_executants')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }
    }
}
