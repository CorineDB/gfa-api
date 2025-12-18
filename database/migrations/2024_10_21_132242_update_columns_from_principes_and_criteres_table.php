<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsFromPrincipesAndCriteresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('principes_de_gouvernance')) {
            Schema::table('principes_de_gouvernance', function (Blueprint $table) {

                if(Schema::hasColumn('principes_de_gouvernance', 'typeDeGouvernanceId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'principes_de_gouvernance' 
                        AND COLUMN_NAME = 'typeDeGouvernanceId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($foreignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['typeDeGouvernanceId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
            
                    $table->renameColumn('typeDeGouvernanceId', 'programmeId');
                    $table->foreign('programmeId')->references('id')->on('programmes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
                else if(!Schema::hasColumn('principes_de_gouvernance', 'programmeId')){
                    $table->bigInteger('programmeId')->unsigned();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
            }); 
        }

        if (Schema::hasTable('criteres_de_gouvernance')) {
            Schema::table('criteres_de_gouvernance', function (Blueprint $table) {

                if(Schema::hasColumn('criteres_de_gouvernance', 'principeDeGouvernanceId')){

                    // Check if the column has a foreign key constraint
                    $cforeignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'criteres_de_gouvernance' 
                        AND COLUMN_NAME = 'principeDeGouvernanceId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($cforeignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['principeDeGouvernanceId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }
            
                    $table->renameColumn('principeDeGouvernanceId', 'programmeId');
                    $table->foreign('programmeId')->references('id')->on('programmes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
                else if(!Schema::hasColumn('criteres_de_gouvernance', 'programmeId')){
                    $table->bigInteger('programmeId')->unsigned();
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

        if (Schema::hasTable('principes_de_gouvernance')) {
            Schema::table('principes_de_gouvernance', function (Blueprint $table) {

                if(Schema::hasColumn('principes_de_gouvernance', 'programmeId')) {

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'principes_de_gouvernance' 
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

                    // Rename column back to original
                    $table->renameColumn('programmeId', 'typeDeGouvernanceId');
                    $table->foreign('typeDeGouvernanceId')->references('id')->on('types_de_gouvernance')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
            });
        }

        if (Schema::hasTable('criteres_de_gouvernance')) {
            Schema::table('criteres_de_gouvernance', function (Blueprint $table) {

                if(Schema::hasColumn('criteres_de_gouvernance', 'programmeId')) {

                    // Check if the column has a foreign key constraint
                    $cforeignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'criteres_de_gouvernance' 
                        AND COLUMN_NAME = 'programmeId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    "));

                    // If a foreign key exists, drop and recreate it
                    if (!empty($cforeignKey)) {

                        // Use try-catch to avoid errors if foreign key doesn't exist
                        try {
                            $table->dropForeign(['programmeId']);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Foreign key didn't exist, no action needed
                        }
                    }

                    // Rename column back to original
                    $table->renameColumn('programmeId', 'principeDeGouvernanceId');
                    $table->foreign('principeDeGouvernanceId')->references('id')->on('principes_de_gouvernance')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                }
            });
        }
    }

}
