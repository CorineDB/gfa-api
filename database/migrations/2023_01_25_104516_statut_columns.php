<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StatutColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activites', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('anos', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('archive_activites', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('archive_composantes', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('archive_projets', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('archive_taches', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('composantes', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });


        Schema::table('projets', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('rappels', function (Blueprint $table) {
            $table->integer('statut')->nullable();
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->integer('statut')->nullable();
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
