<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateForeignKeys extends Migration {

	public function up()
	{
        Schema::table('sinistres', function(Blueprint $table) {
			$table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('unitee_de_gestions', function(Blueprint $table) {
			$table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('projets', function(Blueprint $table) {
			$table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('composantes', function(Blueprint $table) {
			$table->foreign('projetId')->references('id')->on('projets')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('composanteId')->references('id')->on('composantes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('activites', function(Blueprint $table) {
			$table->foreign('composanteId')->references('id')->on('composantes')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('indicateurs', function(Blueprint $table) {

			$table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('categorieId')->references('id')->on('categories')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('uniteeMesureId')->references('id')->on('unitees')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('indicateur_mods', function(Blueprint $table) {
			$table->foreign('modId')->references('id')->on('mods')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('uniteeMesureId')->references('id')->on('unitees')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('categorieId')->references('id')->on('categories')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('maitrise_oeuvres', function(Blueprint $table) {
			$table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('taches', function(Blueprint $table) {
			$table->foreign('activiteId')->references('id')->on('activites')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('plan_de_decaissements', function(Blueprint $table) {
			$table->foreign('activiteId')->references('id')->on('activites')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('suivi_financiers', function(Blueprint $table) {
			$table->foreign('activiteId')->references('id')->on('activites')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

        Schema::table('e_suivies', function(Blueprint $table) {
			$table->foreign('siteId')->references('id')->on('sites')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('missionDeControleId')->references('id')->on('mission_de_controles')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('entrepriseExecutantId')->references('id')->on('entreprise_executants')
						->onDelete('cascade')
						->onUpdate('cascade');


			$table->foreign('checkListId')->references('id')->on('check_lists')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('activiteId')->references('id')->on('e_activites')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('passations', function(Blueprint $table) {
			$table->foreign('entrepriseExecutantId')->references('id')->on('entreprise_executants')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('siteId')->references('id')->on('sites')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('e_activites', function(Blueprint $table) {
			$table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('check_lists', function(Blueprint $table) {

			$table->foreign('uniteeId')->references('id')->on('unitees')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('e_activite_mods', function(Blueprint $table) {
			$table->foreign('modId')->references('id')->on('mods')
						->onDelete('cascade')
						->onUpdate('cascade');
			$table->foreign('siteId')->references('id')->on('sites')
						->onDelete('cascade')
						->onUpdate('cascade');
			$table->foreign('bailleurId')->references('id')->on('bailleurs')
						->onDelete('cascade')
						->onUpdate('cascade');
			$table->foreign('programmeId')->references('id')->on('programmes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('suivi_financier_mods', function(Blueprint $table) {
			$table->foreign('maitriseDoeuvreId')->references('id')->on('maitrise_oeuvres')
						->onDelete('cascade')
						->onUpdate('cascade');
			$table->foreign('siteId')->references('id')->on('sites')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('e_suivi_activite_mods', function(Blueprint $table) {
			$table->foreign('eActiviteModId')->references('id')->on('e_activite_mods')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('proprietes', function(Blueprint $table) {
			$table->foreign('sinistreId')->references('id')->on('sinistres')
						->onDelete('cascade')
						->onUpdate('cascade');
		});


		Schema::table('check_list_com', function(Blueprint $table) {
			$table->foreign('uniteId')->references('id')->on('unitees')
						->onDelete('cascade')
						->onUpdate('cascade');
			$table->foreign('ongComId')->references('id')->on('ong_com')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('payes', function(Blueprint $table) {
			$table->foreign('proprieteId')->references('id')->on('proprietes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('nouvelle_proprietes', function(Blueprint $table) {
			$table->foreign('proprieteId')->references('id')->on('proprietes')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

		Schema::table('com_suivis', function(Blueprint $table) {
			$table->foreign('checkListComId')->references('id')->on('check_list_com')
						->onDelete('cascade')
						->onUpdate('cascade');
		});

        Schema::table('activite_users', function(Blueprint $table) {

			$table->foreign('activiteId')->references('id')->on('activites')
						->onDelete('cascade')
						->onUpdate('cascade');

			$table->foreign('userId')->references('id')->on('users')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
	}

	public function down()
	{

		Schema::table('sinistres', function(Blueprint $table) {
			$table->dropForeign('sinistres_bailleurId_foreign');
		});

		Schema::table('unitee_de_gestions', function(Blueprint $table) {
			$table->dropForeign('unitee_de_gestions_programmeId_foreign');
		});

		Schema::table('projets', function(Blueprint $table) {
			$table->dropForeign('projets_bailleurId_foreign');
			$table->dropForeign('projets_programmeId_foreign');
		});

		Schema::table('composantes', function(Blueprint $table) {
			$table->dropForeign('composantes_projetId_foreign');
			$table->dropForeign('composantes_composanteId_foreign');
		});

		Schema::table('activites', function(Blueprint $table) {
			$table->dropForeign('activites_composanteId_foreign');
			$table->dropForeign('activites_userId_foreign');
		});

		Schema::table('indicateurs', function(Blueprint $table) {
			$table->dropForeign('indicateurs_bailleurId_foreign');
			$table->dropForeign('indicateurs_uniteeMesureId_foreign');
			$table->dropForeign('indicateurs_categorieId_foreign');
		});

		Schema::table('indicateur_mods', function(Blueprint $table) {
			$table->dropForeign('indicateur_mods_modId_foreign');
			$table->dropForeign('indicateurs_mods_uniteeMesureId_foreign');
			$table->dropForeign('indicateurs_mods_categorieId_foreign');
		});

		Schema::table('maitrise_oeuvres', function(Blueprint $table) {
			$table->dropForeign('maitrise_oeuvres_bailleurId_foreign');
		});

		Schema::table('taches', function(Blueprint $table) {
			$table->dropForeign('taches_activiteId_foreign');
		});

		Schema::table('plan_de_decaissements', function(Blueprint $table) {
			$table->dropForeign('plan_de_decaissements_activiteId_foreign');
		});

		Schema::table('suivi_financiers', function(Blueprint $table) {
			$table->dropForeign('suivi_financiers_activiteId_foreign');
		});

		Schema::table('e_suivies', function(Blueprint $table) {
			$table->dropForeign('e_suivies_siteId_foreign');
			$table->dropForeign('e_suivies_missionDeControleId_foreign');
			$table->dropForeign('e_suivies_entrepriseExecutantId_foreign');
			$table->dropForeign('e_suivies_checkListId_foreign');
		});

		Schema::table('passations', function(Blueprint $table) {
			$table->dropForeign('passations_entrepriseExecutantId_foreign');
			$table->dropForeign('passations_siteId_foreign');
		});

		Schema::table('e_activites', function(Blueprint $table) {
			$table->dropForeign('e_activites_programmeId_foreign');
		});

		Schema::table('check_lists', function(Blueprint $table) {
			$table->dropForeign('check_lists_eActiviteId_foreign');
			$table->dropForeign('check_lists_uniteeId_foreign');
		});

		Schema::table('e_activite_mods', function(Blueprint $table) {
			$table->dropForeign('e_activite_mods_modId_foreign');
			$table->dropForeign('e_activite_mods_siteId_foreign');
			$table->dropForeign('e_activite_mods_bailleurId_foreign');
			$table->dropForeign('e_activite_mods_programmeId_foreign');
		});

		Schema::table('suivi_financier_mods', function(Blueprint $table) {
			$table->dropForeign('suivi_financier_mods_maitriseDoeuvreId_foreign');
			$table->dropForeign('suivi_financier_mods_siteId_foreign');
		});

		Schema::table('e_suivi_activite_mods', function(Blueprint $table) {
			$table->dropForeign('e_suivi_activite_mods_eActiviteModId_foreign');
		});

		Schema::table('proprietes', function(Blueprint $table) {
			$table->dropForeign('proprietes_sinistreId_foreign');
		});

		Schema::table('check_list_com', function(Blueprint $table) {
			$table->dropForeign('check_list_com_uniteId_foreign');
			$table->dropForeign('check_list_com_ongComId_foreign');
		});

		Schema::table('payes', function(Blueprint $table) {
			$table->dropForeign('payes_proprieteId_foreign');
		});

		Schema::table('nouvelle_proprietes', function(Blueprint $table) {
			$table->dropForeign('nouvelle_proprietes_proprieteId_foreign');
		});

		Schema::table('com_suivis', function(Blueprint $table) {
			$table->dropForeign('com_suivis_checkListComId_foreign');
		});
	}
}
