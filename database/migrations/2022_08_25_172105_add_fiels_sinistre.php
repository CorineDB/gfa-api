<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFielsSinistre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::hasTable('sinistres', function (Blueprint $table) {
            Schema::table('sinistres', function (Blueprint $table) {
                if (Schema::hasColumn('sinistres', 'prenoms')) {
                    $table->dropColumn('prenoms');
                }

            });
        });

        Schema::table('sinistres', function(Blueprint $table) {

			$table->integer('rue');
            $table->string('sexe');
            $table->string('referencePieceIdentite');
            $table->string('statut');

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
