<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDescriptionToReponsesDeLaCollecteFactuelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if(Schema::hasTable('reponses_de_la_collecte_factuel')){
            Schema::table('reponses_de_la_collecte_factuel', function (Blueprint $table) {
                if(!Schema::hasColumn('reponses_de_la_collecte_factuel', 'description')){
                    $table->longText('description')->nullable();
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
        
        if(Schema::hasTable('suivi_reponses_de_la_collecte_factuelindicateurs')){
            Schema::table('reponses_de_la_collecte_factuel', function (Blueprint $table) {
                if(Schema::hasColumn('reponses_de_la_collecte_factuel', 'sources_de_donnee')){
                    $table->dropColumn('reponses_de_la_collecte_factuel');
                }
            });
        }
    }
}
