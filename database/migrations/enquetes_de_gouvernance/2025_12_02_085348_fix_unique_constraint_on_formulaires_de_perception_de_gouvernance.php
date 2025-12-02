<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixUniqueConstraintOnFormulairesDePerceptionDeGouvernance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formulaires_de_perception_de_gouvernance', function (Blueprint $table) {
            // 🔥 Supprimer l'unique sur la colonne libelle
            $table->dropUnique('formulaires_de_perception_de_gouvernance_libelle_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formulaires_de_perception_de_gouvernance', function (Blueprint $table) {
            // 🔄 Restaurer l'unique si rollback
            $table->unique('libelle', 'formulaires_perception_de_gouvernance_libelle_unique');
        });
    }
}
