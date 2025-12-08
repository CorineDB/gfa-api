<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddfieldReponseAno extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponse_anos', function(Blueprint $table) {

            if (!Schema::hasColumn('reponse_anos', 'auteurId')) {
			    $table->bigInteger('auteurId')->unsigned()->nullable();

                $table->foreign('auteurId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
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
