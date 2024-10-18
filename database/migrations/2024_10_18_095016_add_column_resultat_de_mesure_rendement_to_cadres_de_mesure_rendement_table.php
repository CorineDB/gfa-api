<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnResultatDeMesureRendementToCadresDeMesureRendementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cadres_de_mesure_rendement')) {
            Schema::table('cadres_de_mesure_rendement', function (Blueprint $table) {
                if (!Schema::hasColumn('cadres_de_mesure_rendement', 'resultatCadreDeMesureRendementId')) {
                    $table->bigInteger('resultatCadreDeMesureRendementId')->unsigned();
                    $table->foreign('resultatCadreDeMesureRendementId','resultatParentId')->references('id')->on('resultats_cadre_de_rendement')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('cadres_de_mesure_rendement')) {
            Schema::table('cadres_de_mesure_rendement', function (Blueprint $table) {
                if (!Schema::hasColumn('cadres_de_mesure_rendement', 'resultatCadreDeMesureRendementId')) {
                    $table->dropForeign(['resultatCadreDeMesureRendementId']);
                    $table->dropColumn(['resultatCadreDeMesureRendementId']);
                }
            });
        }
    }
}
