<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldReponseAnos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponse_anos', function(Blueprint $table) {

            if (!Schema::hasColumn('reponse_anos', 'reponseId')) {
			    $table->bigInteger('reponseId')->unsigned()->nullable();

                $table->foreign('reponseId')->references('id')->on('reponse_anos')
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
