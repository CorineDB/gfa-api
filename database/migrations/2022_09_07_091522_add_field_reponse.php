<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldReponse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponses', function(Blueprint $table) {

            if (!Schema::hasColumn('reponses', 'formulaireId')) {
			    $table->bigInteger('formulaireId')->unsigned();

                $table->foreign('formulaireId')->references('id')->on('formulaires')
						->onDelete('cascade')
						->onUpdate('cascade');
            }

            if (!Schema::hasColumn('reponses', 'date')) {
                $table->date('date');
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
