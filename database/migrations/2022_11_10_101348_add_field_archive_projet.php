<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldArchiveProjet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archive_projets', function (Blueprint $table) {
            $table->string('pays')->default('Bénin');
            $table->string('commune')->default('Cotonou');
            $table->string('departement')->default('Litoral');
            $table->string('arrondissement')->default('Sènadé');
            $table->string('quartier')->default('Sènadé');
            $table->string('secteurActivite')->default('Environnement');
            $table->date('dateAprobation')->default(date('Y-m-d'));
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
