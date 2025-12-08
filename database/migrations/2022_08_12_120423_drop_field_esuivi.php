<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFieldEsuivi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('e_suivies', function(Blueprint $table) {
            $table->dropColumn('mois');
            $table->dropColumn('annee');
            $table->date('date');
        });

        Schema::table('e_activite_statuts', function(Blueprint $table) {

			$table->dropColumn('mois');

            $table->dropColumn('annee');

            $table->date('date');

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
