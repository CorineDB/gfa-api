<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFichesDeSyntheseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('fiches_de_synthese')){
            Schema::table('fiches_de_synthese', function (Blueprint $table) {
                if(Schema::hasColumn('fiches_de_synthese', 'reference')){
                    $table->dropColumn('reference');
                }

                if(!Schema::hasColumn('fiches_de_synthese', 'soumissionId')){
                    $table->bigInteger('soumissionId')->nullable()->unsigned();
                    $table->foreign('soumissionId')->references('id')->on('soumissions')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

                if(!Schema::hasColumn('fiches_de_synthese', 'programmeId')){
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
        if(!Schema::hasTable('fiches_de_synthese')){
            Schema::create('fiches_de_synthese', function (Blueprint $table) {
                if(!Schema::hasColumn('fiches_de_synthese', 'reference')){
                    $table->string("reference");
                }

                if(Schema::hasColumn('fiches_de_synthese', 'soumissionId')){
                    $table->dropForeign(['soumissionId']);
                    $table->dropColumn('soumissionId');
                }

                if(!Schema::hasColumn('fiches_de_synthese', 'programmeId')){
                    $table->dropForeign(['programmeId']);
                    $table->dropColumn('programmeId');
                }
            });
        }
    }
}
