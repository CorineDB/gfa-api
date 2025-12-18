<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPreuveIsRequiredToFormulaireOptionsDeReponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formulaire_options_de_reponse', function (Blueprint $table) {
            if(!Schema::hasColumn('formulaire_options_de_reponse', 'preuveIsRequired')){
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
        Schema::table('formulaire_options_de_reponse', function (Blueprint $table) {
            if(Schema::hasColumn('formulaire_options_de_reponse', 'preuveIsRequired')){
                $table->dropColumn('preuveIsRequired');
            }
        });
    }
}
