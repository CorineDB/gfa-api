<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditObjectifAttenduColumnOfEvaluationPrincipesDeGouvernanceObjectifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_principes_de_gouvernance_objectifs', function (Blueprint $table) {
            if(Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'objectif_attendu')){
                $table->json('objectif_attendu')->default(null);
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('evaluation_principes_de_gouvernance_objectifs', function (Blueprint $table) {
            if(Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'objectif_attendu')){
                $table->double('objectif_attendu', 8, 2)->default(0)->change();
            }

        });
    }
}
