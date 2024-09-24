<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropfieldReponse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponses', function(Blueprint $table) {

            if (Schema::hasColumn('reponses', 'annee')) {
                $table->dropColumn('annee');
            }

            if (Schema::hasColumn('reponses', 'mois')) {
                $table->dropColumn('mois');
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
        //
    }
}
