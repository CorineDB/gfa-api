<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUniqueConstrainteOnOptionsDeReponseGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('options_de_reponse_gouvernance', function (Blueprint $table) {
            // 1. Supprimer l’ancienne contrainte unique
            $table->dropUnique('options_de_reponse_gouvernance_libelle_slug_programmeId_unique');

            // 2. Ajouter les nouvelles contraintes uniques
            $table->unique(['libelle', 'type', 'programmeId'], 'options_gouv_libelle_type_programme_unique');
            $table->unique(['slug', 'type', 'programmeId'], 'options_gouv_slug_type_programme_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('options_de_reponse_gouvernance', function (Blueprint $table) {
            // Supprimer les nouvelles contraintes
            $table->dropUnique('options_gouv_libelle_type_programme_unique');
            $table->dropUnique('options_gouv_slug_type_programme_unique');

            // Restaurer l’ancienne contrainte
            $table->unique(['libelle', 'slug', 'programmeId'], 'options_de_reponse_gouvernance_libelle_slug_programmeId_unique');
        });
    }
}
