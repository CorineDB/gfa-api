<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldSinistres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sinistres', function(Blueprint $table) {

			$table->string('modeDePaiement');
            $table->string('longitude');
            $table->string('latitude');
            $table->string('montant');
            $table->string('payer');
            
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
