<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColsNamePta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('composantes', function (Blueprint $table) {
            $table->float('poids')->change();
        });

        Schema::table('activites', function (Blueprint $table) {
            $table->float('poids')->change();
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->float('poids')->change();
        });

        Schema::table('archive_composantes', function (Blueprint $table) {
            $table->float('poids')->change();
        });

        Schema::table('archive_activites', function (Blueprint $table) {
            $table->float('poids')->change();
        });

        Schema::table('archive_taches', function (Blueprint $table) {
            $table->float('poids')->change();
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
