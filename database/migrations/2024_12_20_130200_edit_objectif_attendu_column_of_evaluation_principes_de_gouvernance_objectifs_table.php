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
        Schema::table('evaluation_principes_de_gouvernance_objectifs', function (Blueprint $table) {
            if(Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'objectif_attendu')){
                $table->json('objectif_attendu')->nullable()->change();
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
        Schema::table('evaluation_principes_de_gouvernance_objectifs', function (Blueprint $table) {
            if(Schema::hasColumn('evaluation_principes_de_gouvernance_objectifs', 'objectif_attendu')){
                $table->float('objectif_attendu')->change();
            }

        });
    }
}
