<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldCadreLogique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indicateurs', function(Blueprint $table) {

            if (!Schema::hasColumn('indicateurs', 'hypothese')) {
			    $table->string('hypothese')->nullable();
            }

            if (!Schema::hasColumn('indicateurs', 'sourceDeVerification')) {
			    $table->string('sourceDeVerification')->nullable();
            }
		});

        Schema::table('resultats', function(Blueprint $table) {

            if (!Schema::hasColumn('resultats', 'indicateurId')) {
			    $table->bigInteger('indicateurId')->unsigned();
                $table->foreign('indicateurId')->references('id')->on('indicateurs')
				  ->onDelete('cascade')
				  ->onUpdate('cascade');
            }
		});

        Schema::table('objectif_specifiques', function(Blueprint $table) {

            if (!Schema::hasColumn('objectif_specifiques', 'indicateurId')) {
			    $table->bigInteger('indicateurId')->unsigned();
                $table->foreign('indicateurId')->references('id')->on('indicateurs')
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
