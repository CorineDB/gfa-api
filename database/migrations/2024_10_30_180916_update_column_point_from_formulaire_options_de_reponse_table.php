<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnPointFromFormulaireOptionsDeReponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('formulaire_options_de_reponse')){
            Schema::table('formulaire_options_de_reponse', function (Blueprint $table) {
                if(Schema::hasColumn('formulaire_options_de_reponse', 'point')){
                    $table->float('point')->change();
                }
            });
        }

        if(Schema::hasTable('reponses_de_la_collecte')){
            Schema::table('reponses_de_la_collecte', function (Blueprint $table) {
                if(Schema::hasColumn('reponses_de_la_collecte', 'point')){
                    $table->float('point')->change();
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
        if(Schema::hasTable('formulaire_options_de_reponse')){
            Schema::table('formulaire_options_de_reponse', function (Blueprint $table) {
                if(Schema::hasColumn('formulaire_options_de_reponse', 'point')){
                    $table->integer('point')->change();
                }
            });
        }
        
        if(Schema::hasTable('reponses_de_la_collecte')){
            Schema::table('reponses_de_la_collecte', function (Blueprint $table) {
                if(Schema::hasColumn('reponses_de_la_collecte', 'point')){
                    $table->integer('point')->change();
                }
            });
        }
    }
}
