<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColumnAddressToSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('sites')){
            Schema::table('sites', function (Blueprint $table) {

                if(!Schema::hasColumn('sites', 'quartier')){
                    $table->string('quartier')->default('Sènadé');
                }

                if(!Schema::hasColumn('sites', 'arrondissement')){
                    $table->string('arrondissement')->default('Cotonou');
                }

                if(!Schema::hasColumn('sites', 'commune')){
                    $table->string('commune')->default('Cotonou');
                }

                if(!Schema::hasColumn('sites', 'departement')){
                    $table->string('departement')->default('Litoral');
                }

                if(!Schema::hasColumn('sites', 'pays')){
                    $table->string('pays')->default('Bénin');
                }

                // Check if the column exists
                if (Schema::hasColumn('sites', 'programmeId')) {
                    // Check for existing foreign key
                    $foreignKey = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'sites' 
                        AND COLUMN_NAME = 'programmeId' 
                        AND CONSTRAINT_SCHEMA = DATABASE()
                    ");

                    if (!empty($foreignKey)) {
                        // Drop the foreign key if it exists
                        $foreignKeyName = $foreignKey[0]->CONSTRAINT_NAME;
                        $table->dropForeign([$foreignKeyName]);
                    }

                    // Update the column
                    $table->bigInteger('programmeId')->unsigned()->nullable()->change();

                    // Add the foreign key
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                } else {
                    // Create the column if it doesn't exist
                    $table->bigInteger('programmeId')->unsigned()->nullable();

                    // Add the foreign key
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

                /* if(Schema::hasColumn('sites', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'sites' 
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
                        $table->bigInteger('programmeId')->unsigned()->nullable()->default(NULL)->change();

                        // Recreate the foreign key
                        $table->foreign('programmeId')->references('id')->on('programmes')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');

                    }
                }
                else{
                    // Alter the column and set a new foreign key to programmes
                    $table->bigInteger('programmeId')->unsigned()->nullable()->default(NULL);
                    $table->foreign('programmeId')->references('id')->on('programmes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                } */
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
        if(Schema::hasTable('sites')){
            Schema::table('sites', function (Blueprint $table) {

                if(Schema::hasColumn('sites', 'quartier')){
                    $table->dropColumn('quartier');
                }

                if(Schema::hasColumn('sites', 'arrondissement')){
                    $table->dropColumn('arrondissement');
                }

                if(Schema::hasColumn('sites', 'commune')){
                    $table->dropColumn('commune');
                }

                if(Schema::hasColumn('sites', 'departement')){
                    $table->dropColumn('departement');
                }

                if(Schema::hasColumn('sites', 'pays')){
                    $table->dropColumn('pays');
                }

                if(Schema::hasColumn('sites', 'programmeId')){

                    // Check if the column has a foreign key constraint
                    $foreignKey = \DB::select(\DB::raw("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'sites' 
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
