<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldSuiviIndicateurMod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suivi_indicateur_mods', function(Blueprint $table) {

            if (!Schema::hasColumn('suivi_indicateur_mods', 'dateSuivie')) {
			    $table->datetime('dateSuivie');
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
