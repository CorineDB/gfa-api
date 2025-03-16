<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPreuveIsRequiredToReponsesDeLaCollecteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponses_de_la_collecte', function (Blueprint $table) {
            if(!Schema::hasColumn('reponses_de_la_collecte', 'preuveIsRequired')){
                $table->boolean('preuveIsRequired')->default(false);
            }
            else{
                $table->boolean('preuveIsRequired')->default(false)->change();
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
        Schema::table('reponses_de_la_collecte', function (Blueprint $table) {
            if(Schema::hasColumn('reponses_de_la_collecte', 'preuveIsRequired')){
                $table->dropColumn('preuveIsRequired');
            }
        });
    }
}
