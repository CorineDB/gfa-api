<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSuiviIndicateur extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suivi_indicateurs', function(Blueprint $table) {

            if (Schema::hasColumn('suivi_indicateurs', 'dateSuivie')) {
			    $table->datetime('dateSuivie')->change();
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
