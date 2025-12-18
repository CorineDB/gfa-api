<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsOfIndicateursCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(!Schema::hasColumn('indicateurs', 'indice')){
                    $table->integer('indice');
                }

                if(Schema::hasColumn('indicateurs', 'responsable')){
                    $table->dropColumn('responsable');
                }
            });
        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {

                if(Schema::hasColumn('categories', 'nom')){
                    // ou dropIndex selon le cas


                    /* try {
                        // 🔥 SUPPRESSION FORCÉE DES INDEX SUR nom
                        \DB::statement('DROP INDEX categories_nom_unique ON categories');
                        //\DB::statement('DROP INDEX categories_nom_index ON categories');
                    } catch (\Throwable $e) {
                        // ignore si déjà supprimé
                    } */
                    $table->longText('nom')->change();
                }

                if(!Schema::hasColumn('categories', 'indice')){
                    $table->integer('indice');
                }

                if (!Schema::hasColumn('categories', 'type')) {
                    $table->enum('type', ['impact', 'effet', 'produit'])->default('produit');
                }

                if(!Schema::hasColumn('categories', 'categorieId')){
                    $table->bigInteger('categorieId')->nullable()->unsigned();
                    $table->foreign('categorieId')->references('id')->on('categories')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                
                }

                if(Schema::hasColumn('categories', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'categories' 
                        AND COLUMN_NAME = 'programmeId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['programmeId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    else {
            
                        // Alter the column and set a new foreign key to organisations
                        $table->bigInteger('programmeId')->unsigned()->nullable()->change();
    
                        // Recreate the foreign key
                        $table->foreign('programmeId')->references('id')->on('programmes')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');

                    }
                }
                else{
                    // Alter the column and set a new foreign key to programmes
                    $table->bigInteger('programmeId')->nullable()->unsigned();
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
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(Schema::hasColumn('indicateurs', 'indice')){
                    $table->dropColumn('indice');
                }
        
                if(!Schema::hasColumn('indicateurs', 'responsable')){
                    $table->string('responsable');
                }

            });
        }

        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {

                if(Schema::hasColumn('categories', 'indice')){
                    $table->dropColumn('indice');
                }

                if(Schema::hasColumn('categories', 'type')){
                    $table->dropColumn('type');
                }

                if(Schema::hasColumn('categories', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'categories' 
                        AND COLUMN_NAME = 'programmeId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['programmeId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
                    $table->dropColumn('programmeId');
                }
            });
        }
    }
}
