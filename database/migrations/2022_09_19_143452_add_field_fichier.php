<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldFichier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fichiers', function(Blueprint $table) {

            if (!Schema::hasColumn('fichiers', 'auteurId')) {
			    $table->bigInteger('auteurId')->unsigned();

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
