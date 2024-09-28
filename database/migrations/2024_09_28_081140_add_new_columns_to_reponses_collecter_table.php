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

                    $table->dropForeign(['organisationId']);
                    $table->dropColumn('organisationId');
                }

                if(Schema::hasColumn('reponses_collecter', 'note')){
                    $table->dropColumn('note');
                }

            });
        }
    }
}
