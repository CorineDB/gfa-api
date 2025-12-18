<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIndiceDeGouvernanceToFichesDeSyntheseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("fiches_de_synthese")) {
            Schema::table('fiches_de_synthese', function (Blueprint $table) {
                if (!Schema::hasColumn('fiches_de_synthese', 'indice_de_gouvernance')) {
                    $table->float('indice_de_gouvernance')->default(0);
                }
                if (!Schema::hasColumn('fiches_de_synthese', 'resultats')) {
                    $table->jsonb("resultats")->nullable();
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
        if (Schema::hasTable("fiches_de_synthese")) {
            Schema::table('fiches_de_synthese', function (Blueprint $table) {

                if (Schema::hasColumn('fiches_de_synthese', 'indice_de_gouvernance')) {
                    $table->dropColumn('indice_de_gouvernance');
                }
                if (Schema::hasColumn('fiches_de_synthese', 'resultats')) {
                    $table->dropColumn('resultats');
                }
            });
        }
    }
}
