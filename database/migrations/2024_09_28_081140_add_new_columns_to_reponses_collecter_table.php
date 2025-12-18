<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToReponsesCollecterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('reponses_collecter')){
            Schema::table('reponses_collecter', function (Blueprint $table) {

                if(Schema::hasColumn('reponses_collecter', 'entrepriseExecutantId')){

                    $table->dropColumn('entrepriseExecutantId');
                }

                if(!Schema::hasColumn('reponses_collecter', 'organisationId')){
                    $table->bigInteger('organisationId')->unsigned()->nullable()->after("enqueteDeCollecteId");
                    $table->foreign('organisationId')->references('id')->on('entreprise_executants')
                                ->onDelete('cascade')
                                ->onUpdate('cascade');
                }

                if(!Schema::hasColumn('reponses_collecter', 'note')){
                    $table->string('note')->before("source");
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
        if(Schema::hasTable('reponses_collecter')){
            Schema::table('reponses_collecter', function (Blueprint $table) {

                if(Schema::hasColumn('reponses_collecter', 'entrepriseExecutantId')){

                    $table->dropColumn('entrepriseExecutantId');
                }

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

                    $table->dropColumn('organisationId');
                }

                if(Schema::hasColumn('reponses_collecter', 'note')){
                    $table->dropColumn('note');
                }

            });
        }
    }
}
